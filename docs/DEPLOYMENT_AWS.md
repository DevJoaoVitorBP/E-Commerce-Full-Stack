🌐 [English](DEPLOYMENT_AWS.en.md) | Português (BR)

# 🚀 Deployment na AWS - Guia Operacional

**Guia passo a passo para deployar o projeto na AWS Free Tier**

> ⚠️ **Antes de usar este guia:** substitua todos os valores entre `<>` pelos seus próprios dados. Nunca commite o arquivo `.env` com credenciais reais no repositório — adicione-o ao `.gitignore`.

---

## 📍 URLs de Produção

```
Frontend:  https://<seu-dominio>.duckdns.org
API:       https://<seu-dominio>.duckdns.org/api/v1
Swagger:   https://<seu-dominio>.duckdns.org/api/documentation
```

---

## 🏗️ Arquitetura Infraestrutura

```
DuckDNS Domain
      ↓
EC2 t2.micro (Ubuntu 24.04)
├── Nginx (Port 80/443 - HTTPS Let's Encrypt)
├── PHP 8.2 + Laravel API (Port 8000)
├── Redis Alpine (Port 6379)
└── Queue Worker
      ↓
RDS PostgreSQL t2.micro
   Database: <nome-do-banco>
```

---

## 📋 Pré-requisitos

- Conta AWS (Free Tier ativado)
- DuckDNS (grátis) ou seu próprio domínio
- Access Key AWS (opcional, para CLI)

---

## 🔧 1. Criar EC2 t2.micro

### Via AWS Console

1. **EC2 Dashboard** → "Launch instances"
2. **Name**: `ecommerce-prod`
3. **AMI**: Ubuntu Server 24.04 LTS (x86)
4. **Instance type**: `t2.micro` ✅ Free tier
5. **Key pair**: Criar `<nome-da-sua-key>.pem` (salve seguro! nunca versione este arquivo)
6. **Network settings**:
   - VPC: default
   - Auto-assign Public IP: ✅ Enable
7. **Security Group** (criar novo):
   ```
   Inbound Rules:
   - SSH (22):     My IP
   - HTTP (80):    0.0.0.0/0
   - HTTPS (443):  0.0.0.0/0
   - Custom (8000): 0.0.0.0/0 (teste, depois remover)
   ```
8. **Storage**: 30GB (padrão, grátis)
9. **Launch**

### Elastic IP (opcional, recomendado)

```bash
# Console AWS > Elastic IPs > Allocate
# Associe à instância EC2
```

---

## 🔧 2. Criar RDS PostgreSQL t2.micro

### Via AWS Console

1. **RDS Dashboard** → "Create database"
2. **Engine**: PostgreSQL 15
3. **Free tier template**: ✅ Selecionar
4. **DB instance identifier**: `ecommerce-db`
5. **Master username**: `<seu-usuario>`
6. **Master password**: gere uma senha forte e única (ex: via gerenciador de senhas) — **não reutilize senhas de outros serviços**
7. **DB instance class**: `db.t2.micro` ✅
8. **Storage**:
   - Type: gp2
   - Allocated: 20GB
   - Auto scaling: ❌ Desabilitar
9. **Connectivity**:
   - VPC: default
   - Public accessibility: recomendado **❌ No** — libere acesso apenas via Security Group da EC2 (veja abaixo). Só use "Yes" temporariamente se precisar acessar de fora para debug, e desative depois.
10. **Security Group**: Criar `ecommerce-db-sg`
11. **Database name**: `<nome-do-banco>`
12. **Create**

### Permitir EC2 Acessar RDS

1. Security Group `ecommerce-db-sg` → Inbound rules
2. Add rule:
   ```
   Type: PostgreSQL (5432)
   Source: sg-xxxxx (security group da EC2)
   ```

---

## 🔧 3. Provisionar EC2

### SSH na Instância

```bash
# Windows/Mac/Linux
ssh -i <nome-da-sua-key>.pem ubuntu@<seu-ip-publico>

# Ou use EC2 Instance Connect (browser)
```

### Instalar Dependências

```bash
sudo apt update && sudo apt upgrade -y

# PHP 8.2 + Extensões
sudo apt install -y software-properties-common
sudo add-apt-repository ppa:ondrej/php
sudo apt update
sudo apt install -y \
  php8.2-cli php8.2-fpm php8.2-pgsql \
  php8.2-xml php8.2-mbstring php8.2-zip \
  php8.2-curl php8.2-gd

# Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Node.js
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs

# Docker (Redis)
sudo apt install -y docker.io
sudo usermod -aG docker ubuntu

# Nginx
sudo apt install -y nginx

# Git
sudo apt install -y git

# Supervisor
sudo apt install -y supervisor

# Certbot (HTTPS)
sudo apt install -y certbot python3-certbot-nginx
```

---

## 🔧 4. Clonar e Configurar Backend

```bash
cd /home/ubuntu
git clone https://github.com/<seu-usuario>/<seu-repositorio>.git
cd <seu-repositorio>/backend

# Instalar dependências
composer install --no-dev --optimize-autoloader

# .env
cp .env.example .env
nano .env
```

**Edite `.env` com (substitua todos os valores):**
```bash
APP_NAME="E-Commerce API"
APP_ENV=production
APP_KEY=                     # gerado no próximo passo com php artisan key:generate
APP_DEBUG=false
APP_URL=https://<seu-dominio>.duckdns.org

DB_CONNECTION=pgsql
DB_HOST=<seu-rds-endpoint>.rds.amazonaws.com
DB_PORT=5432
DB_DATABASE=<nome-do-banco>
DB_USERNAME=<seu-usuario>
DB_PASSWORD=<sua-senha-forte-aqui>

CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379

QUEUE_CONNECTION=database

MAIL_MAILER=log
```

> 🔒 **Nunca commite o arquivo `.env`.** Confirme que ele está listado no `.gitignore` antes de subir qualquer alteração.

**Configurar banco:**
```bash
php artisan key:generate
php artisan migrate:fresh --seed
php artisan storage:link
```

---

## 🔧 5. Clonar e Configurar Frontend

```bash
cd ../frontend

# .env
nano .env
```

**Edite `.env`:**
```bash
VITE_API_URL=https://<seu-dominio>.duckdns.org/api/v1
```

**Build:**
```bash
npm install
npm run build

# Copiar para Nginx
sudo mkdir -p /var/www/ecommerce
sudo cp -r dist/* /var/www/ecommerce/
sudo chown -R www-data:www-data /var/www/ecommerce
sudo chmod -R 755 /var/www/ecommerce
```

---

## 🔧 6. Configurar Nginx

```bash
sudo nano /etc/nginx/sites-available/ecommerce
```

**Cole (substitua o domínio):**
```nginx
server {
    listen 80 default_server;
    server_name _;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2 default_server;
    server_name _;

    ssl_certificate /etc/letsencrypt/live/<seu-dominio>.duckdns.org/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/<seu-dominio>.duckdns.org/privkey.pem;

    # Security headers
    add_header Strict-Transport-Security "max-age=31536000" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-Frame-Options "DENY" always;

    location ~ ^/api/ {
        proxy_pass http://127.0.0.1:8000;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }

    location ~ ^/storage/ {
        proxy_pass http://127.0.0.1:8000;
    }

    location / {
        root /var/www/ecommerce;
        try_files $uri $uri/ /index.html;
    }
}
```

**Ativar:**
```bash
sudo ln -sf /etc/nginx/sites-available/ecommerce /etc/nginx/sites-enabled/
sudo rm -f /etc/nginx/sites-enabled/default
sudo nginx -t
sudo systemctl restart nginx
```

---

## 🔧 7. Configurar Supervisor (Processos em Background)

### Laravel API

```bash
sudo nano /etc/supervisor/conf.d/laravel-api.conf
```

```ini
[program:laravel-api]
process_name=%(program_name)s_%(process_num)02d
command=php /home/ubuntu/<seu-repositorio>/backend/artisan serve --host=0.0.0.0 --port=8000
autostart=true
autorestart=true
user=ubuntu
numprocs=1
redirect_stderr=true
stdout_logfile=/var/log/laravel-api.log
```

### Queue Worker

```bash
sudo nano /etc/supervisor/conf.d/laravel-queue.conf
```

```ini
[program:laravel-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /home/ubuntu/<seu-repositorio>/backend/artisan queue:work database --sleep=1
autostart=true
autorestart=true
user=ubuntu
numprocs=1
redirect_stderr=true
stdout_logfile=/var/log/laravel-queue.log
```

### Redis

```bash
sudo nano /etc/supervisor/conf.d/redis.conf
```

```ini
[program:redis]
process_name=%(program_name)s
command=docker run --rm --name redis -p 6379:6379 redis:alpine
autostart=true
autorestart=true
user=ubuntu
stdout_logfile=/var/log/redis.log
stderr_logfile=/var/log/redis-error.log
```

**Ativar:**
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl status
```

Deve aparecer 3 processos em `RUNNING` ✅

---

## 🔧 8. Configurar DNS + HTTPS

### DuckDNS (Gratuito)

1. Acesse: https://www.duckdns.org
2. Sign in com GitHub/Google
3. Create domain: `<seu-dominio>`
4. Cole seu IP público da EC2
5. Clique "Add domain"

### Let's Encrypt

```bash
sudo certbot --nginx -d <seu-dominio>.duckdns.org
# Email: <seu-email>
# Agree: Y
# Share: N

# Verificar renovação automática
sudo systemctl status certbot.timer
```

**Testar:**
```bash
https://<seu-dominio>.duckdns.org
```

Deve aparecer 🔒 verde!

---

## 📊 Monitoramento & Manutenção

### Verificar Status

```bash
# Supervisor
sudo supervisorctl status

# Nginx
sudo systemctl status nginx

# Logs
tail -f /var/log/laravel-api.log
tail -f /var/log/laravel-queue.log
tail -f /var/log/redis.log
tail -f /var/log/nginx/error.log
```

### Restart Services

```bash
# Todos
sudo supervisorctl restart all

# Individual
sudo supervisorctl restart laravel-api
sudo supervisorctl restart laravel-queue
sudo supervisorctl restart redis

# Nginx
sudo systemctl restart nginx
```

### RDS Backup

```bash
# AWS Console > RDS > Databases > ecommerce-db > Actions > Create snapshot
# Ou via CLI:
aws rds create-db-snapshot \
  --db-instance-identifier ecommerce-db \
  --db-snapshot-identifier ecommerce-backup-$(date +%Y%m%d)
```

---

## 🆘 Troubleshooting

### "Queue worker não conecta Redis"
```bash
# Redis não está rodando
sudo supervisorctl status redis
# Se FATAL: reinicie
sudo supervisorctl restart redis
```

### "Mixed content HTTPS/HTTP error"
```bash
# Frontend ainda aponta para HTTP
# Edite frontend/.env
VITE_API_URL=https://<seu-dominio>.duckdns.org/api/v1
# Rebuild e redeploy
npm run build
sudo cp -r dist/* /var/www/ecommerce/
```

### "Certificado SSL não funciona"
```bash
# Verificar domínio DuckDNS
# 1. DuckDNS deve apontar para IP correto
# 2. Espere 5-10 min para propagação
# 3. Teste: nslookup <seu-dominio>.duckdns.org
```

### "Imagens não aparecem"
```bash
# Nginx não está fazendo proxy de /storage/
# Verifique config nginx e reinicie
sudo nginx -t
sudo systemctl restart nginx
```

---

## 💰 Custos Estimados

### 12 Meses Free Tier
- EC2 t2.micro: $0 (750h/mês)
- RDS t2.micro: $0 (750h/mês)
- S3: $0 (5GB)
- DuckDNS: $0
- Let's Encrypt: $0
- **Total: $0** ✅

### Após Free Tier (mensal)
- EC2 t2.micro: ~$9.50
- RDS t2.micro: ~$15
- S3 (5GB): ~$0.12
- Data Transfer: ~$0-5
- **Total: ~$24-30/mês**

---

## 🔒 Boas Práticas de Segurança

- Gere a senha do RDS com um gerenciador de senhas — nunca reutilize senhas de outros serviços.
- Mantenha `Public accessibility: No` no RDS; libere acesso apenas via Security Group da EC2.
- Remova a regra da porta 8000 (`0.0.0.0/0`) do Security Group assim que os testes de deploy terminarem — o Nginx já faz o proxy internamente.
- Adicione `.env` ao `.gitignore` em ambos os projetos (backend e frontend) e confirme que nenhum `.env` foi versionado.
- Restrinja o acesso SSH (porta 22) ao seu IP, evitando `0.0.0.0/0`.
- Rotacione credenciais periodicamente e sempre que houver suspeita de exposição.

---

## ✅ Checklist Deployment

- [ ] EC2 criada e rodando
- [ ] RDS PostgreSQL criada (sem acesso público)
- [ ] `.env` configurado localmente e **fora** do controle de versão
- [ ] Backend clonado e configurado
- [ ] Frontend built e servido via Nginx
- [ ] Supervisor com 3 processos (api, queue, redis)
- [ ] DuckDNS domínio apontando para EC2
- [ ] Let's Encrypt certificado ativo
- [ ] Nginx com HTTPS funcionando
- [ ] Porta 8000 removida do Security Group após testes
- [ ] Frontend acessível em https://domínio
- [ ] API respondendo em /api/v1/products
- [ ] Swagger documentação acessível

---

## 📈 Próximas Melhorias

- [ ] AWS S3 para imagens (libera espaço EC2)
- [ ] Elastic Load Balancer + autoscaling
- [ ] CloudFront CDN
- [ ] SQS para fila distribuída
- [ ] CloudWatch alertas
- [ ] Multi-region redundância

---

## 📞 Suporte Rápido

| Problema | Comando |
|----------|---------|
| Ver logs API | `tail -f /var/log/laravel-api.log` |
| Reiniciar tudo | `sudo supervisorctl restart all` |
| Testar BD | `psql -h <seu-rds-endpoint> -U <seu-usuario> -d <nome-do-banco>` |
| Limpar cache | `php artisan cache:clear` |
| Nova migration | `php artisan migrate` |

---

**Última atualização**: 2026-07-22
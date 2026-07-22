🌐 English | [Português (BR)](DEPLOYMENT_AWS.md_)

# 🚀 AWS Deployment - Operations Guide

**Step-by-step guide to deploy the project on AWS Free Tier**

> ⚠️ **Before using this guide:** replace all values between `<>` with your own data. Never commit the `.env` file with real credentials to the repository — add it to `.gitignore`.

---

## 📍 Production URLs

```
Frontend:  https://<your-domain>.duckdns.org
API:       https://<your-domain>.duckdns.org/api/v1
Swagger:   https://<your-domain>.duckdns.org/api/documentation
```

---

## 🏗️ Infrastructure Architecture

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
   Database: <database-name>
```

---

## 📋 Prerequisites

- AWS account (Free Tier activated)
- DuckDNS (free) or your own domain
- AWS Access Key (optional, for CLI)

---

## 🔧 1. Create EC2 t2.micro

### Via AWS Console

1. **EC2 Dashboard** → "Launch instances"
2. **Name**: `ecommerce-prod`
3. **AMI**: Ubuntu Server 24.04 LTS (x86)
4. **Instance type**: `t2.micro` ✅ Free tier
5. **Key pair**: Create `<your-key-name>.pem` (save it securely! never version this file)
6. **Network settings**:
   - VPC: default
   - Auto-assign Public IP: ✅ Enable
7. **Security Group** (create new):
   ```
   Inbound Rules:
   - SSH (22):     My IP
   - HTTP (80):    0.0.0.0/0
   - HTTPS (443):  0.0.0.0/0
   - Custom (8000): 0.0.0.0/0 (testing only, remove afterwards)
   ```
8. **Storage**: 30GB (default, free)
9. **Launch**

### Elastic IP (optional, recommended)

```bash
# AWS Console > Elastic IPs > Allocate
# Associate with the EC2 instance
```

---

## 🔧 2. Create RDS PostgreSQL t2.micro

### Via AWS Console

1. **RDS Dashboard** → "Create database"
2. **Engine**: PostgreSQL 15
3. **Free tier template**: ✅ Select
4. **DB instance identifier**: `ecommerce-db`
5. **Master username**: `<your-username>`
6. **Master password**: generate a strong, unique password (e.g. via a password manager) — **never reuse passwords from other services**
7. **DB instance class**: `db.t2.micro` ✅
8. **Storage**:
   - Type: gp2
   - Allocated: 20GB
   - Auto scaling: ❌ Disable
9. **Connectivity**:
   - VPC: default
   - Public accessibility: recommended **❌ No** — allow access only via the EC2 Security Group (see below). Only use "Yes" temporarily if you need external access for debugging, then disable it afterwards.
10. **Security Group**: Create `ecommerce-db-sg`
11. **Database name**: `<database-name>`
12. **Create**

### Allow EC2 to Access RDS

1. Security Group `ecommerce-db-sg` → Inbound rules
2. Add rule:
   ```
   Type: PostgreSQL (5432)
   Source: sg-xxxxx (EC2 security group)
   ```

---

## 🔧 3. Provision EC2

### SSH into the Instance

```bash
# Windows/Mac/Linux
ssh -i <your-key-name>.pem ubuntu@<your-public-ip>

# Or use EC2 Instance Connect (browser)
```

### Install Dependencies

```bash
sudo apt update && sudo apt upgrade -y

# PHP 8.2 + Extensions
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

## 🔧 4. Clone and Configure the Backend

```bash
cd /home/ubuntu
git clone https://github.com/<your-username>/<your-repository>.git
cd <your-repository>/backend

# Install dependencies
composer install --no-dev --optimize-autoloader

# .env
cp .env.example .env
nano .env
```

**Edit `.env` with (replace all values):**
```bash
APP_NAME="E-Commerce API"
APP_ENV=production
APP_KEY=                     # generated in the next step with php artisan key:generate
APP_DEBUG=false
APP_URL=https://<your-domain>.duckdns.org

DB_CONNECTION=pgsql
DB_HOST=<your-rds-endpoint>.rds.amazonaws.com
DB_PORT=5432
DB_DATABASE=<database-name>
DB_USERNAME=<your-username>
DB_PASSWORD=<your-strong-password-here>

CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379

QUEUE_CONNECTION=database

MAIL_MAILER=log
```

> 🔒 **Never commit the `.env` file.** Confirm it's listed in `.gitignore` before pushing any changes.

**Set up the database:**
```bash
php artisan key:generate
php artisan migrate:fresh --seed
php artisan storage:link
```

---

## 🔧 5. Clone and Configure the Frontend

```bash
cd ../frontend

# .env
nano .env
```

**Edit `.env`:**
```bash
VITE_API_URL=https://<your-domain>.duckdns.org/api/v1
```

**Build:**
```bash
npm install
npm run build

# Copy to Nginx
sudo mkdir -p /var/www/ecommerce
sudo cp -r dist/* /var/www/ecommerce/
sudo chown -R www-data:www-data /var/www/ecommerce
sudo chmod -R 755 /var/www/ecommerce
```

---

## 🔧 6. Configure Nginx

```bash
sudo nano /etc/nginx/sites-available/ecommerce
```

**Paste (replace the domain):**
```nginx
server {
    listen 80 default_server;
    server_name _;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2 default_server;
    server_name _;

    ssl_certificate /etc/letsencrypt/live/<your-domain>.duckdns.org/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/<your-domain>.duckdns.org/privkey.pem;

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

**Enable:**
```bash
sudo ln -sf /etc/nginx/sites-available/ecommerce /etc/nginx/sites-enabled/
sudo rm -f /etc/nginx/sites-enabled/default
sudo nginx -t
sudo systemctl restart nginx
```

---

## 🔧 7. Configure Supervisor (Background Processes)

### Laravel API

```bash
sudo nano /etc/supervisor/conf.d/laravel-api.conf
```

```ini
[program:laravel-api]
process_name=%(program_name)s_%(process_num)02d
command=php /home/ubuntu/<your-repository>/backend/artisan serve --host=0.0.0.0 --port=8000
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
command=php /home/ubuntu/<your-repository>/backend/artisan queue:work database --sleep=1
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

**Enable:**
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl status
```

3 processes should show `RUNNING` ✅

---

## 🔧 8. Configure DNS + HTTPS

### DuckDNS (Free)

1. Go to: https://www.duckdns.org
2. Sign in with GitHub/Google
3. Create domain: `<your-domain>`
4. Paste your EC2 public IP
5. Click "Add domain"

### Let's Encrypt

```bash
sudo certbot --nginx -d <your-domain>.duckdns.org
# Email: <your-email>
# Agree: Y
# Share: N

# Verify automatic renewal
sudo systemctl status certbot.timer
```

**Test:**
```bash
https://<your-domain>.duckdns.org
```

You should see a green 🔒!

---

## 📊 Monitoring & Maintenance

### Check Status

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
# All
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
# Or via CLI:
aws rds create-db-snapshot \
  --db-instance-identifier ecommerce-db \
  --db-snapshot-identifier ecommerce-backup-$(date +%Y%m%d)
```

---

## 🆘 Troubleshooting

### "Queue worker can't connect to Redis"
```bash
# Redis isn't running
sudo supervisorctl status redis
# If FATAL: restart it
sudo supervisorctl restart redis
```

### "Mixed content HTTPS/HTTP error"
```bash
# Frontend still points to HTTP
# Edit frontend/.env
VITE_API_URL=https://<your-domain>.duckdns.org/api/v1
# Rebuild and redeploy
npm run build
sudo cp -r dist/* /var/www/ecommerce/
```

### "SSL certificate doesn't work"
```bash
# Check DuckDNS domain
# 1. DuckDNS must point to the correct IP
# 2. Wait 5-10 min for propagation
# 3. Test: nslookup <your-domain>.duckdns.org
```

### "Images don't appear"
```bash
# Nginx isn't proxying /storage/
# Check the nginx config and restart it
sudo nginx -t
sudo systemctl restart nginx
```

---

## 💰 Estimated Costs

### First 12 Months (Free Tier)
- EC2 t2.micro: $0 (750h/month)
- RDS t2.micro: $0 (750h/month)
- S3: $0 (5GB)
- DuckDNS: $0
- Let's Encrypt: $0
- **Total: $0** ✅

### After Free Tier (monthly)
- EC2 t2.micro: ~$9.50
- RDS t2.micro: ~$15
- S3 (5GB): ~$0.12
- Data Transfer: ~$0-5
- **Total: ~$24-30/month**

---

## 🔒 Security Best Practices

- Generate the RDS password with a password manager — never reuse passwords from other services.
- Keep `Public accessibility: No` on RDS; allow access only via the EC2 Security Group.
- Remove the port 8000 rule (`0.0.0.0/0`) from the Security Group as soon as deployment testing is done — Nginx already proxies it internally.
- Add `.env` to `.gitignore` in both projects (backend and frontend) and confirm no `.env` file has been committed.
- Restrict SSH access (port 22) to your IP, avoiding `0.0.0.0/0`.
- Rotate credentials periodically and whenever exposure is suspected.

---

## ✅ Deployment Checklist

- [ ] EC2 created and running
- [ ] RDS PostgreSQL created (no public access)
- [ ] `.env` configured locally and **outside** of version control
- [ ] Backend cloned and configured
- [ ] Frontend built and served via Nginx
- [ ] Supervisor with 3 processes (api, queue, redis)
- [ ] DuckDNS domain pointing to EC2
- [ ] Let's Encrypt certificate active
- [ ] Nginx with HTTPS working
- [ ] Port 8000 removed from Security Group after testing
- [ ] Frontend accessible at https://domain
- [ ] API responding at /api/v1/products
- [ ] Swagger documentation accessible

---

## 📈 Next Improvements

- [ ] AWS S3 for images (frees up EC2 space)
- [ ] Elastic Load Balancer + autoscaling
- [ ] CloudFront CDN
- [ ] SQS for distributed queue
- [ ] CloudWatch alerts
- [ ] Multi-region redundancy

---

## 📞 Quick Support

| Issue | Command |
|----------|---------|
| View API logs | `tail -f /var/log/laravel-api.log` |
| Restart everything | `sudo supervisorctl restart all` |
| Test DB | `psql -h <your-rds-endpoint> -U <your-username> -d <database-name>` |
| Clear cache | `php artisan cache:clear` |
| New migration | `php artisan migrate` |

---

**Last updated**: 2026-07-22
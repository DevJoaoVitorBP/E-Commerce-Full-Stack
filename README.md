🌐 [English](docs/README.en.md) | Português (BR)

# E-Commerce Fullstack

## Sobre o projeto

Aplicação fullstack que simula um fluxo completo de e-commerce — desde a navegação e filtragem de produtos até o checkout com controle de estoque e envio de e-mail de confirmação via fila. O backend expõe uma API REST com 33 endpoints e o frontend consome essa API por meio de uma SPA em Vue 3, com painel administrativo separado por papel de usuário.

🔗 **Demo em produção**: [ecommerce-projeto.duckdns.org](https://ecommerce-projeto.duckdns.org)

## Funcionalidades

- Cadastro e autenticação de usuários via JWT (Laravel Sanctum)
- Listagem de produtos com filtros por categoria, faixa de preço e busca textual
- Carrinho de compras persistido por usuário autenticado
- Checkout com validação de estoque em tempo real e registro de movimentação
- Histórico e detalhe de pedidos com controle de status
- Painel administrativo para gerenciamento de produtos, categorias e pedidos
- Upload e remoção de imagem de produto
- Alerta de estoque baixo no painel admin
- E-mail de confirmação de pedido processado de forma assíncrona via fila
- Documentação interativa da API com Swagger UI

## Tecnologias utilizadas

**Linguagem**
- PHP 8.2
- TypeScript

**Framework / Biblioteca**
- Laravel 11
- Vue 3 (Composition API)
- Pinia
- TanStack Query v5
- Vue Router 5
- Axios
- vee-validate + Zod

**Banco de dados**
- SQLite (padrão) / MySQL (opcional)

**Cache**
- Redis (tagged cache com invalidação seletiva)

**Estilização**
- Tailwind CSS 4
- Headless UI
- Lucide Vue

**Testes**
- PHPUnit (107 testes — 85.15% de cobertura de linhas)
- Vitest + happy-dom (12 testes)

**Ferramentas**
- Vite 8 (build + proxy de desenvolvimento)
- Laravel Pint (PSR-12)
- ESLint + Prettier
- GitHub Actions (CI/CD)
- Docker (Redis)

**Outros recursos técnicos**
- Laravel Sanctum (Bearer Token)
- Laravel Events, Listeners e Jobs (fila assíncrona)
- API Resources (formatação e filtragem de resposta)
- Form Requests com regras customizadas (`UniqueSlug`, `SufficientStock`)
- Swagger / OpenAPI 3.0.0

## Como executar o projeto

### Pré-requisitos

- PHP 8.2+
- Composer 2.5+
- Node.js 18+ e npm 9+
- Docker (para o container Redis)

### Backend

```bash
cd backend

composer install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
php artisan storage:link

# Terminal 1 — servidor da API
php artisan serve --host=0.0.0.0 --port=8000

# Terminal 2 — processador de filas (opcional, necessário para e-mails)
php artisan queue:work database --sleep=1
```

### Redis

```bash
# Primeira vez
docker run -d --name ecommerce-redis -p 6379:6379 redis:alpine

# Execuções seguintes
docker start ecommerce-redis
```

### Frontend

```bash
cd frontend

npm install

# Terminal 3 — servidor de desenvolvimento
npm run dev
```

### URLs de acesso

| Serviço | URL |
|---------|-----|
| Frontend | `http://localhost:5173` |
| API | `http://localhost:8000/api/v1` |
| Swagger UI | `http://localhost:8000/api/documentation` |

### Credenciais de teste

| Perfil | E-mail | Senha |
|--------|--------|-------|
| Admin | admin@example.com | password |
| Usuário | test@example.com | password |

## Organização do projeto

```
challenge-fullstack-pleno/
├── backend/          # Laravel 11 — API REST
│   └── app/
│       ├── Http/Controllers/Api/V1/  # Recebem a requisição e delegam
│       ├── Services/                 # Lógica de negócio
│       ├── Repositories/             # Abstração do acesso ao banco
│       ├── DTOs/                     # Transferência de dados entre camadas
│       ├── Models/                   # Modelos Eloquent e relacionamentos
│       ├── Events/ + Listeners/      # Fluxo de eventos (pedido criado, estoque baixo)
│       ├── Jobs/                     # Tarefas assíncronas (e-mail, estoque)
│       ├── Rules/                    # Regras de validação customizadas
│       ├── Policies/                 # Autorização por recurso
│       └── Http/Resources/           # Formatação e filtragem das respostas JSON
│
└── frontend/         # Vue 3 + TypeScript — SPA
    └── src/
        ├── pages/        # 14 páginas lazy-loaded (públicas, auth e admin)
        ├── components/   # Componentes reutilizáveis e modais
        ├── stores/       # Estado global com Pinia (auth, cart, products, orders)
        ├── composables/  # Integração TanStack Query (cache e invalidação)
        ├── services/     # Cliente Axios com interceptors de token e redirect
        ├── schemas/      # Validação de formulários com Zod
        └── router/       # Rotas com guards requiresAuth e requiresAdmin
```

O backend segue a separação em camadas Controller → Service → Repository → Model. O frontend separa estado (Pinia), busca de dados (TanStack Query) e apresentação (páginas + componentes), com validação de formulários via Zod.

## O que este projeto demonstra

- Estruturação de API REST com versionamento, resources e documentação OpenAPI
- Aplicação do padrão Repository + Service Layer em PHP, favorecendo testabilidade e separação de responsabilidades
- Autenticação e autorização por papel (admin / usuário) com Laravel Sanctum e Policies
- Uso de filas e eventos para desacoplar operações assíncronas (envio de e-mail, atualização de estoque)
- Cache distribuído com Redis e invalidação seletiva por tags
- Gerenciamento de estado reativo no frontend com Pinia e TanStack Query
- Validação de esquemas em TypeScript com Zod integrado ao vee-validate
- Escrita de testes automatizados no backend (PHPUnit, 85% de cobertura) e no frontend (Vitest)
- Configuração de pipeline CI/CD com GitHub Actions

## Melhorias futuras

- Adicionar refresh token para prolongar sessões sem novo login
- Implementar paginação no painel admin de pedidos
- Expandir cobertura de testes no frontend para as páginas principais
- Adicionar suporte a múltiplas imagens por produto
- Criar endpoint de relatório de vendas por período para o painel admin

## Autor
 
**João Pereira**
 
[![LinkedIn](https://img.shields.io/badge/LinkedIn-devjoaopereira-0A66C2?logo=linkedin&logoColor=white)](https://www.linkedin.com/in/devjoaopereira/)
[![GitHub](https://img.shields.io/badge/GitHub-DevJoaoVitorBP-181717?logo=github&logoColor=white)](https://github.com/DevJoaoVitorBP)
[![E-mail](https://img.shields.io/badge/E--mail-developerjoaopereira%40gmail.com-D14836?logo=gmail&logoColor=white)](mailto:developerjoaopereira@gmail.com)
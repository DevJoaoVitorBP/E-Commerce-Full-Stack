# Projeto E-Commerce — Documentação Técnica

Sistema de e-commerce fullstack desenvolvido com **Laravel 11** (PHP 8.2) no backend e **Vue 3 + TypeScript** no frontend, seguindo princípios SOLID e padrões de design (Repository, Service Layer, DTO).

| Camada | Stack | Testes |
|--------|-------|--------|
| Backend | Laravel 11, PHP 8.2, SQLite | 107 testes, 85.15% coverage |
| Frontend | Vue 3, TypeScript, Pinia, TanStack Query | 12 testes |
| API | 33 endpoints, Swagger/OpenAPI 3.0.0 | Documentação interativa |

**Versão:** 2.0.0 · **Atualizado em:** Julho 2026

---

## Índice

1. [Como Executar](#como-executar)
2. [Documentação da API](#documentação-da-api)
3. [Frontend — Vue 3 + TypeScript](#frontend--vue-3--typescript)
4. [Arquitetura](#arquitetura)
5. [Estrutura de Diretórios](#estrutura-de-diretórios)
6. [Segurança & Autenticação](#segurança--autenticação)
7. [Endpoints da API](#endpoints-da-api)
8. [Performance & Otimização](#performance--otimização)
9. [Fluxo de Negócio](#fluxo-de-negócio)
10. [Banco de Dados](#banco-de-dados)
11. [Testes](#testes)
12. [Boas Práticas & Padrões de Código](#boas-práticas--padrões-de-código)
13. [Dependências](#dependências)
14. [Configurações do Projeto](#configurações-do-projeto)
15. [Jobs & Email de Confirmação](#jobs--email-de-confirmação)
16. [Decisões Arquiteturais](#decisões-arquiteturais)
17. [CI/CD Pipeline](#cicd-pipeline)
18. [Troubleshooting](#troubleshooting)
19. [Exemplos de Uso](#exemplos-de-uso)

---

## Como Executar

### Pré-requisitos

| Requisito | Versão Mínima | Observação |
|-----------|---------------|-----------|
| PHP | 8.2+ | Testado em PHP 8.2 |
| Composer | 2.5+ | Gerenciador de dependências PHP |
| Node.js | 18+ | Para o frontend |
| npm | 9+ | Gerenciador de pacotes JS |
| SQLite | qualquer | Configurado por padrão |

### Instalação (Backend + Frontend)

```bash
# 1. Clone e entre no diretório raiz
cd ecommerce-api

# ─── BACKEND ──────────────────────────────────────────
cd backend

# 2. Instale dependências PHP
composer install

# 3. Configure variáveis de ambiente
cp .env.example .env
php artisan key:generate

# 4. Execute migrações e seeds
php artisan migrate:fresh --seed

# 4.1. Crie o link simbólico para o storage público
php artisan storage:link

# 5. Inicie o servidor da API (Terminal 1)
php artisan serve --host=0.0.0.0 --port=8000

# Opcional: processar filas de jobs em background (Terminal 2)
php artisan queue:work database --sleep=1

# ─── FRONTEND ─────────────────────────────────────────
cd ../frontend

# 6. Instale dependências JS
npm install

# 7. Inicie o servidor de desenvolvimento (Terminal 3)
npm run dev
```

### URLs de Acesso

| Serviço | URL | Observação |
|---------|-----|-----------|
| Frontend (dev) | `http://localhost:5173` | Servidor Vite |
| API Backend | `http://localhost:8000/api/v1` | Laravel |
| Swagger UI | `http://localhost:8000/api/documentation` | Docs interativas |
| OpenAPI JSON | `http://localhost:8000/api/openapi.json` | Spec para Postman/Insomnia |

### Redis via Docker (obrigatório)

O cache da aplicação usa Redis, executado via Docker no container `ecommerce-redis`.

**Primeira vez (criar o container):**
```bash
docker run -d \
  --name ecommerce-redis \
  -p 6379:6379 \
  redis:alpine
```

**Nas próximas vezes:**
```bash
docker start ecommerce-redis
```

**Verificar se está rodando:**
```bash
docker ps | grep ecommerce-redis
```

> O `.env` já está configurado com `CACHE_STORE=redis`, `REDIS_HOST=127.0.0.1` e `REDIS_PORT=6379`. Nenhuma alteração adicional é necessária.

### Credenciais de Teste

| Tipo | Email | Senha |
|------|-------|-------|
| Admin | admin@example.com | password |
| Usuário Regular | test@example.com | password |

---

## Documentação da API

A API está documentada com Swagger/OpenAPI 3.0.0.

**Interface Interativa (Swagger UI)** — `http://localhost:8000/api/documentation`
- 33 endpoints documentados, testáveis diretamente pela interface
- Schemas de request/response e autenticação Bearer Token integrada

**Especificação JSON** — `http://localhost:8000/api/openapi.json`
- Compatível com Postman, Insomnia, Swagger Editor
- Versão: 1.0.0 · Servidor base: `http://localhost:8000/api/v1`

**Importar em ferramentas externas:**
- *Postman*: File → Import → Link → cole a URL do JSON
- *Insomnia*: File → Import → URL → cole a URL do JSON
- *Swagger Editor* ([editor.swagger.io](https://editor.swagger.io)): File → Import URL

**Testar endpoints protegidos:**
1. Clique em **"Authorize"** no topo da Swagger UI
2. Cole um Bearer token (obtido via `/auth/login`)
3. Clique em **"Authorize"** novamente — os endpoints autenticados ficam disponíveis

---

## Frontend — Vue 3 + TypeScript

Interface web em Vue 3 (Composition API), TypeScript, Tailwind CSS e TanStack Query, consumindo a API REST do backend com gerenciamento de estado via Pinia.

### Tecnologias & Justificativas

| Biblioteca | Versão | Justificativa |
|-----------|--------|---------------|
| Vue 3 | 3.5.38 | Composition API e melhor suporte a TypeScript |
| TypeScript | ~6.0.2 | Erros detectados em tempo de compilação |
| Pinia | 3.0.4 | State management oficial do Vue 3, tipado, pouco boilerplate |
| TanStack Query | 5.101.2 | Cache de requisições, staleTime configurável, invalidação automática |
| Vue Router | 5.1.0 | Guards de navegação para autenticação/admin |
| Tailwind CSS | 4.3.1 | Utility-first, bundle pequeno com purge automático |
| Axios | 1.18.1 | Interceptors para token e redirect em 401/403 |
| Zod | 3.25.76 | Schema validation com inferência de tipos |
| vee-validate | 4.15.1 | Validação de formulários integrada a schemas Zod |
| Headless UI | 1.7.23 | Componentes acessíveis sem estilo |
| Lucide Vue | 1.0.0 | Ícones SVG |
| Vite | 8.1.0 | Build tool com HMR e proxy para o backend |
| Vitest | 4.1.9 | Test runner compatível com Vite |

### Comandos

```bash
cd frontend

npm install              # instalar dependências
npm run dev              # desenvolvimento com HMR
npm run typecheck        # checagem de tipos TypeScript
npm run lint:check       # lint (ESLint + Vue)
npm run lint             # lint com auto-fix
npm run format:check     # checagem Prettier
npm run format           # formatação com auto-fix
npm run build             # build de produção
npm run preview          # preview do build
```

### Testes do Frontend

```bash
cd frontend

npm run test           # modo watch
npm run test:run       # execução única (CI)
npm run test:ui        # interface gráfica Vitest UI
npm run test:coverage  # com coverage
```

**Última execução registrada:** 2 arquivos de teste, 12 testes, ~700ms.

- `src/tests/stores/authStore.test.ts` — testes da store de autenticação
- `src/tests/stores/cartStore.test.ts` — testes da store de carrinho

### Estrutura de Pastas do Frontend

```
frontend/
├── public/                        # Assets públicos (favicons, etc.)
├── src/
│   ├── main.ts                    # Bootstrap: Pinia, Router, TanStack Query, mount
│   ├── App.vue                    # Componente raiz: Header, <router-view>, Footer, Toast
│   ├── style.css                  # Estilos globais + diretivas Tailwind
│   ├── env.d.ts                   # Tipos para variáveis de ambiente Vite
│   │
│   ├── pages/                     # 14 páginas (lazy-loaded pelo router)
│   │   ├── Home.vue               # Hero, produtos em destaque, stats
│   │   ├── Products.vue           # Listagem com filtros + paginação
│   │   ├── ProductDetail.vue      # Detalhe do produto + adicionar ao carrinho
│   │   ├── Cart.vue               # Carrinho com edição de quantidade (auth)
│   │   ├── Checkout.vue           # Formulário de pedido com endereços (auth)
│   │   ├── Orders.vue             # Histórico de pedidos com paginação (auth)
│   │   ├── OrderDetail.vue        # Detalhe completo do pedido (auth)
│   │   ├── Profile.vue            # Edição de perfil e senha (auth)
│   │   ├── Login.vue              # Formulário de login
│   │   ├── Register.vue           # Formulário de cadastro
│   │   ├── AdminDashboard.vue     # Painel admin: estatísticas, alertas (admin)
│   │   ├── AdminProducts.vue      # CRUD de produtos (admin)
│   │   ├── AdminCategories.vue    # CRUD de categorias (admin)
│   │   └── AdminOrders.vue        # Gerenciamento de pedidos (admin)
│   │
│   ├── components/
│   │   ├── common/
│   │   │   ├── Header.vue         # Navbar com logo, search, auth menu, contador carrinho
│   │   │   ├── Footer.vue         # Rodapé com links e copyright
│   │   │   ├── Toast.vue          # Notificações (success/error/info/warning)
│   │   │   ├── LoadingSpinner.vue # Skeleton de carregamento
│   │   │   └── LowStockAlert.vue  # Alerta de estoque baixo/crítico para admin
│   │   └── modals/
│   │       ├── BaseModal.vue          # Modal base reutilizável
│   │       ├── AddProductModal.vue    # Formulário criar produto
│   │       ├── EditProductModal.vue   # Formulário editar produto
│   │       └── EditCategoryModal.vue  # Formulário editar categoria
│   │
│   ├── stores/                    # Estado global com Pinia
│   │   ├── authStore.ts           # user, token, login/logout/register/updateProfile
│   │   ├── cartStore.ts           # cart, items, CRUD carrinho, itemCount, total
│   │   ├── productsStore.ts       # products, categories, cache 5min, CRUD (admin)
│   │   └── ordersStore.ts         # orders, cache 5min, createOrder, updateStatus
│   │
│   ├── composables/               # Integração TanStack Query
│   │   ├── useProductsQuery.ts    # Lista de produtos com filtros reativos
│   │   ├── useProductQuery.ts     # Produto individual por ID
│   │   ├── useCategoriesQuery.ts  # Categorias (staleTime 24h)
│   │   ├── useOrdersQuery.ts      # Pedidos do usuário com filtros
│   │   ├── useOrderQuery.ts       # Pedido individual por ID
│   │   ├── useLowStockProducts.ts # Produtos com estoque baixo (admin)
│   │   └── useNotification.ts     # Sistema de notificações globais
│   │
│   ├── services/
│   │   ├── api.ts                 # Axios com baseURL, interceptors (token, 401/403)
│   │   └── api-debug.ts           # Versão com logging detalhado para debug
│   │
│   ├── types/                     # Product, Category, Cart, Order, User, ApiResponse, etc.
│   ├── schemas/                   # Schemas Zod para validação de formulários
│   ├── utils/                     # errorHandler.ts, validation.ts
│   ├── router/index.ts            # 14 rotas, guards requiresAuth/requiresAdmin
│   └── tests/
│       ├── setup.ts
│       └── stores/
│           ├── authStore.test.ts
│           └── cartStore.test.ts
│
├── index.html
├── vite.config.ts                 # proxy /api → localhost:8000, alias @
├── vitest.config.ts                # happy-dom, coverage v8
├── tailwind.config.js
├── tsconfig.json                  # strict mode
├── eslint.config.js
└── package.json
```

### Decisões de Implementação Notáveis

**Interceptors Axios centralizados:**
```typescript
// Injeção automática de token
api.interceptors.request.use(config => {
  const token = localStorage.getItem('auth_token');
  if (token) config.headers.Authorization = `Bearer ${token}`;
  return config;
});

// Redirect automático em 401/403
api.interceptors.response.use(null, error => {
  if (error.response?.status === 401) router.push('/login');
  if (error.response?.status === 403) router.push('/');
});
```

**Route guards declarativos:**
```typescript
// Meta na rota
meta: { requiresAuth: true, requiresAdmin: true }

// Guard global único
router.beforeEach((to) => {
  if (to.meta.requiresAuth && !authStore.isAuthenticated) → /login
  if (to.meta.requiresAdmin && !authStore.isAdmin) → /home
});
```

**Proxy Vite (evita CORS em dev):**
```typescript
// vite.config.ts
server: {
  proxy: { '/api': 'http://localhost:8000' }
}
```

### Configuração de Ambiente

```env
# .env (frontend) - opcional
VITE_API_BASE_URL=http://localhost:8000/api/v1
```

O frontend usa `http://localhost:8000/api/v1` como baseURL padrão no Axios.

---

## Arquitetura

### Visão Geral Fullstack

```
┌──────────────────────────────────────────────────────────┐
│                   FRONTEND (Vue 3 + TS)                  │
│  Pages → Components → Composables (TanStack Query)       │
│  Pinia Stores → Axios Services → Router Guards           │
└─────────────────────────┬────────────────────────────────┘
                          │ HTTP / REST JSON
┌─────────────────────────▼────────────────────────────────┐
│               BACKEND (Laravel 11 API)                   │
│  Routes → Controllers → Services → Repositories → Models │
│  Events → Listeners → Jobs (Queue) → Email               │
└─────────────────────────┬────────────────────────────────┘
                          │ SQLite / MySQL
┌─────────────────────────▼────────────────────────────────┐
│                       DATABASE                           │
│  users, products, categories, orders, cart, jobs, etc.  │
└──────────────────────────────────────────────────────────┘
```

### Camadas do Backend

| Camada | Local | Responsabilidade |
|--------|-------|-------------------|
| Presentation (Controllers) | `app/Http/Controllers/Api/V1/` | Recebe requisições HTTP, formata respostas |
| Service (lógica de negócio) | `app/Services/` | Coordena operações e validações — `ProductService`, `CartService`, `OrderService`, `CategoryService`, `ImageService` |
| Repository (acesso a dados) | `app/Repositories/` | Abstração do banco — `ProductRepository`, `CategoryRepository`, `OrderRepository`, `CartRepository` |
| Model | `app/Models/` | Estrutura de dados e relacionamentos Eloquent |

### Padrões de Design

**Backend:** Repository Pattern, Service Layer, DTOs, Factory (testes), Observer (Events/Listeners), Job Pattern (filas).

**Frontend:** Composable Pattern (TanStack Query), Store Pattern (Pinia), Presenter/Container (páginas como containers, componentes como presenters), Guard Pattern (rotas).

---

## Estrutura de Diretórios

```
ecommerce-api/
├── backend/                           # Laravel 11 API
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/Api/V1/   # Controllers da API v1
│   │   │   ├── Middleware/           # AdminMiddleware, HandleCors
│   │   │   ├── Requests/             # Form Requests de validação
│   │   │   └── Resources/            # Resources para formatação JSON
│   │   ├── Models/                   # Modelos Eloquent
│   │   ├── Services/                 # Camada de serviços (+ ImageService)
│   │   ├── Repositories/             # Camada de repositórios
│   │   ├── DTOs/                     # Data Transfer Objects
│   │   ├── Events/                   # Eventos da aplicação
│   │   ├── Listeners/                # Listeners de eventos
│   │   ├── Jobs/                     # Jobs para fila
│   │   ├── Rules/                    # Regras de validação customizadas
│   │   ├── Policies/                 # Policies de autorização
│   │   └── Traits/                   # Traits reutilizáveis (ApiResponses)
│   ├── database/
│   │   ├── migrations/               # Migrações do banco
│   │   ├── factories/                # Factories para testes
│   │   └── seeders/                  # Seeders de dados
│   ├── routes/
│   │   ├── api.php                   # Rotas da API
│   │   └── web.php                   # Rotas web
│   ├── tests/                        # Testes automatizados
│   └── config/                       # Arquivos de configuração
│
└── frontend/                          # Vue 3 + TypeScript SPA
    └── src/
        ├── pages/                     # 14 páginas (lazy-loaded)
        ├── components/                # Componentes reutilizáveis
        ├── stores/                    # Estado global (Pinia)
        ├── composables/               # Integração TanStack Query
        ├── services/                  # Axios API client
        ├── types/                     # Tipos TypeScript
        ├── schemas/                   # Schemas Zod
        ├── utils/                     # Utilitários
        ├── router/                    # Vue Router + guards
        └── tests/                     # Testes Vitest
```

---

## Segurança & Autenticação

### Autenticação (Laravel Sanctum)

- **Método:** Personal Access Tokens · **Tipo:** Bearer Token · **Header:** `Authorization: Bearer {token}`
- **Endpoints públicos:** `/auth/register`, `/auth/login`
- **Endpoints protegidos:** middleware `auth:sanctum`

**Fluxo:**
1. `POST /auth/register` — registra novo usuário
2. `POST /auth/login` — retorna token (plain text)
3. Token usado no header `Authorization`
4. `POST /auth/logout` — revoga o token
5. `GET /auth/me` — retorna dados do usuário autenticado

### Autorização

- **AdminMiddleware** verifica `is_admin = true`; aplicado a rotas de criação/atualização/deleção. Retorna `403 Forbidden` sem permissão.
- **Produtos e Categorias:** apenas admins podem `POST`, `PUT`, `DELETE`.
- **Pedidos:** apenas admins podem atualizar status.
- **Carrinho:** apenas o dono pode acessar.
- **Policies** cobrem autorização granular adicional.

### Validação

**Form Requests:** `StoreProductRequest`, `UpdateProductRequest`, `StoreCategoryRequest`, `CreateOrderRequest`, `AddToCartRequest`.

**Regras customizadas:** `UniqueSlug` (slug único por categoria), validações de preço/quantidade, checagem de disponibilidade de estoque.

### Outras Proteções

- **CORS** habilitado via middleware `HandleCors`
- **Rate limiting** aplicado a endpoints da API
- **HTTPS** recomendado em produção
- **Expiração de token** configurável via Sanctum

---

## Endpoints da API

Base URL: `http://localhost:8000/api/v1` — 33 endpoints no total.

### Autenticação (5)
```
POST   /auth/register           # Registrar novo usuário
POST   /auth/login              # Fazer login
POST   /auth/logout             # Logout (autenticado)
GET    /auth/me                 # Dados do usuário (autenticado)
PUT    /auth/profile            # Atualizar perfil (autenticado)
```

### Produtos (9)
```
GET    /products                # Listar com filtros (público)
GET    /products/{id}           # Obter detalhes (público)
GET    /products/low-stock      # Estoque baixo (admin)
POST   /products                # Criar (admin)
PUT    /products/{id}           # Atualizar (admin)
POST   /products/{id}           # Atualizar via FormData com _method: PUT (admin)
DELETE /products/{id}           # Deletar (admin)
POST   /products/{id}/image     # Upload de imagem (admin)
DELETE /products/{id}/image     # Remover imagem (admin)
```

**Filtros disponíveis em `GET /products`:** `category_id`, `search`, `min_price`, `max_price`, `sort`, `per_page`.

### Categorias (6)
```
GET    /categories              # Listar com hierarquia (público)
GET    /categories/{id}         # Obter categoria (público)
GET    /categories/{id}/products # Produtos da categoria (público)
POST   /categories              # Criar (admin)
PUT    /categories/{id}         # Atualizar (admin)
DELETE /categories/{id}         # Deletar (admin)
```

### Carrinho (5 — autenticado)
```
GET    /cart                    # Obter carrinho
POST   /cart/items              # Adicionar item
PUT    /cart/items/{itemId}     # Atualizar quantidade
DELETE /cart/items/{itemId}     # Remover item
DELETE /cart                    # Limpar carrinho
```

### Pedidos (4 — autenticado)
```
GET    /orders                  # Listar pedidos do usuário
GET    /orders/{id}             # Obter detalhes do pedido
POST   /orders                  # Criar pedido
PUT    /orders/{id}/status      # Atualizar status (admin)
```

**Status válidos:** `pending`, `processing`, `shipped`, `delivered`, `cancelled`

---

## Performance & Otimização

### API Resources

`ProductResource`, `CategoryResource`, `CartResource`, `CartItemResource`, `OrderResource`, `OrderItemResource` — ocultam dados sensíveis (hashes, timestamps internos), convertem tipos (Carbon → ISO 8601) e carregam relacionamentos automaticamente.

### Query Optimization

- **Eager loading** (`with('items.product')`) evita o problema de N+1 queries.
- **Índices no banco:** `products` (category_id, created_at), `orders` (user_id, created_at, status), `cart_items` (cart_id), `stock_movements` (product_id, type).
- **Cache com TTL via Redis:** produtos por 1 hora (invalidado ao atualizar), categorias por 24 horas, com tagged cache para invalidação seletiva.

### Structured Logging

Log estruturado em JSON nos principais eventos: `ProductCreated`, `OrderCreated`, `OrderItemCreated`, `StockMovement`, `SendOrderConfirmationEmail`.

```json
{
  "message": "Product created",
  "context": {
    "product_id": 1,
    "name": "Product Name",
    "price": 99.99
  }
}
```

---

## Fluxo de Negócio

### Criação de Pedido

1. Usuário adiciona itens ao carrinho
2. Usuário faz checkout criando um pedido
3. Sistema valida disponibilidade de estoque
4. Cria registro de pedido no banco
5. Cria itens do pedido
6. Atualiza quantidade em estoque
7. Registra movimento de estoque
8. Limpa carrinho do usuário
9. Dispara evento `OrderCreated`
10. Listener envia email de confirmação (async via fila)

### Controle de Estoque

- `Product::lowStock()` — scope para produtos com estoque baixo
- `Product::inStock()` — scope para produtos com estoque disponível
- `StockMovement` — rastreia todas as mudanças de estoque (entrada, saída, ajuste, venda, devolução)

---

## Banco de Dados

### Modelos Principais

- **Product** — has one Category, belongsToMany Tags, hasMany OrderItems, hasMany StockMovements
- **Order** — belongsTo User, hasMany OrderItems
- **Cart** — belongsTo User, hasMany CartItems

### Relacionamentos

```
┌─────────┐  1:N  ┌────────────┐
│Category │◄─────►│ Product    │
└─────────┘       └────────────┘
                       │ M:N
                   ┌───────┐
                   │ Tags  │
                   └───────┘

┌──────┐  1:N  ┌───────┐  1:N  ┌──────────────┐
│ User │◄─────►│ Order │◄─────►│ OrderItem    │
└──────┘       └───────┘       └──────────────┘
                                      │ N:1
                              ┌───────────────┐
                              │ Product       │
                              └───────────────┘
```

---

## Testes

### Backend — PHPUnit

Última execução: **107 testes, 186 assertions, 100% de sucesso.** Coverage: 85.15% de linhas (814/956), 66.34% de métodos (136/205), 50.00% de classes (30/60).

```bash
cd backend
php artisan test              # todos os testes
./vendor/bin/phpunit          # diretamente via PHPUnit
php artisan test --compact    # execução rápida, sem coverage
```

**Cobertura por área:**
- Autenticação (5) — register, login, logout, perfil, validações
- Produtos (5+) — CRUD, filtros, busca, validações
- Categorias (6) — CRUD, hierarquia, relacionamentos
- Carrinho (4) — add, update, remove, clear
- Pedidos (8) — criar, listar, atualizar status, validações, permissões
- Email de confirmação (3) — envio, dados corretos, tratamento de erro
- Processamento de pedidos (4) — sucesso, sem usuário, sem itens, total inválido
- Atualização de estoque (6) — sucesso, StockMovement, estoque insuficiente, múltiplos itens, evento StockLow
- Listeners/eventos (5) — SendOrderNotification, LogProductCreation, NotifyAdminLowStock
- Repositories (11) — CartRepository, CategoryRepository, ProductRepository, OrderRepository
- Unit: ProductService (4), CartService (4)

**Coverage em HTML** (requer Xdebug):
```bash
./vendor/bin/phpunit --coverage-html=coverage-report
open coverage-report/index.html    # macOS/Linux
start coverage-report/index.html   # Windows
```

**Coverage em texto:**
```bash
./vendor/bin/phpunit --coverage-text
```

### Frontend — Vitest

Última execução: **12 testes passando** em 2 arquivos.

```bash
cd frontend
npm run test           # modo watch
npm run test:run       # execução única (CI)
npm run test:ui        # interface gráfica
npm run test:coverage  # com coverage
```

| Arquivo | Testes | Cobre |
|---------|--------|-----------|
| `src/tests/stores/authStore.test.ts` | 6 | Login, logout, register, isAdmin, persistência de token |
| `src/tests/stores/cartStore.test.ts` | 6 | Add item, remove, update quantity, total, clear |

```typescript
// vitest.config.ts
{
  environment: 'happy-dom',
  coverage: { provider: 'v8' }
}
```

---

## Boas Práticas & Padrões de Código

### PHP — PSR-12 com Laravel Pint

```bash
./vendor/bin/pint --test        # verificar conformidade
./vendor/bin/pint               # corrigir automaticamente
./vendor/bin/pint --test app/Models/User.php   # arquivo específico
./vendor/bin/pint --verbose     # modo detalhado
```

**Padrões aplicados:** indentação de 4 espaços, linha máxima de 120 caracteres, namespaces e use statements organizados, camelCase para métodos e snake_case para variáveis.

```php
<?php

namespace App\Services;

use App\Models\Order;
use App\Repositories\OrderRepository;
use Illuminate\Support\Facades\Mail;

class OrderService
{
    public function __construct(
        private OrderRepository $repository,
    ) {}

    public function createOrder(array $data): Order
    {
        $order = $this->repository->create($data);

        Mail::queue(new OrderConfirmation($order));

        return $order;
    }
}
```

Recomenda-se adicionar `./vendor/bin/pint --test` ao pipeline de CI, bloqueando o deploy em caso de falha.

### JavaScript / Frontend

- **ESLint** (`eslint.config.js`) com plugins Vue 3 e TypeScript
- **Prettier** para formatação automática de `.ts`, `.vue` e `.json`

```bash
npm run lint:check      # verificar
npm run format:check    # verificar
npm run lint            # corrigir
npm run format          # corrigir
```

---

## Dependências

### Backend (PHP / Laravel)

**Core:** Laravel 11, Laravel Sanctum 4.3.2 (autenticação via Personal Access Tokens), PHP 8.2+

**Documentação/API:** L5-Swagger 11.1.0, Zircote/Swagger-PHP 6.2.0

**Dev & Qualidade:** PHPUnit 11.5.50, Laravel Tinker 2.10.1, Laravel Pint 1.24, Faker, Collision 8.6, SQLite, Redis (via predis), fila via Database Queue Driver

### Frontend (JavaScript / Vue)

**Core:** Vue 3.5.38, TypeScript ~6.0.2, Vite 8.1.0

**Estado & Data Fetching:** Pinia 3.0.4, @tanstack/vue-query 5.101.2

**Roteamento & HTTP:** vue-router 5.1.0, axios 1.18.1

**Formulários:** vee-validate 4.15.1, @vee-validate/zod 4.15.1, zod 3.25.76

**UI:** Tailwind CSS 4.3.1, @headlessui/vue 1.7.23, lucide-vue-next 1.0.0

**Testes (dev):** Vitest 4.1.9, @testing-library/vue 8.1.0, happy-dom 20.10.6, @vitest/coverage-v8

**Qualidade de código (dev):** ESLint 10.5.0, eslint-plugin-vue, typescript-eslint, Prettier 3.8.5, vue-tsc 3.3.5

---

## Configurações do Projeto

### `.env` (padrão)

```env
APP_NAME="E-Commerce API"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite

CACHE_STORE=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
QUEUE_CONNECTION=database

LOG_CHANNEL=stack
LOG_LEVEL=debug
```

### Cache

- **Driver:** Redis (via predis, host `127.0.0.1:6379`)
- **TTL produtos:** 1 hora · **TTL categorias:** 24 horas
- **Invalidação:** automática ao atualizar/deletar dados

### Filas

- **Driver:** Database (tabela `jobs`)
- **Processar:** `php artisan queue:work database --sleep=1`

---

## Jobs & Email de Confirmação

### Jobs Implementados

1. **UpdateStockAfterOrder** — disparado na criação de um pedido; atualiza estoque, cria `StockMovement` para auditoria, dispara evento `StockLow` se necessário; usa transações e falha (com rollback) se o estoque for insuficiente.
2. **ProcessOrder** — validação de pagamento (mockado), integração com gateway (placeholder), confirmação de estoque; log estruturado de sucesso/erro.
3. **SendOrderConfirmationEmail** — envia email de confirmação ao cliente; log de sucesso/erro da entrega.

O listener `SendOrderNotification` dispara os três jobs quando o evento `OrderCreated` é acionado, em background, sem bloquear a resposta HTTP.

```bash
# Terminal 1: inicie a fila
php artisan queue:work database

# Terminal 2: crie um pedido via API
curl -X POST http://localhost:8000/api/v1/orders \
  -H "Authorization: Bearer {token}" \
  -d "..."

# Os 3 jobs são processados automaticamente no Terminal 1
```

### Email de Confirmação de Pedido

Configurado para envio via **Mailtrap** (sandbox SMTP para desenvolvimento):

```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=seu_token_aqui
MAIL_PASSWORD=seu_token_aqui
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@ecommerce.com
MAIL_FROM_NAME="Meu E-Commerce"
```

**Componentes:**
- `app/Mail/OrderConfirmation.php` — Mailable com dados do pedido (número, itens, endereços, tracking); template em `resources/views/emails/order-confirmation.blade.php`
- `app/Jobs/SendOrderConfirmationEmail.php` — envio assíncrono via `ShouldQueue`, com validação, log e retry automático
- `app/Listeners/SendOrderNotification.php` — escuta `OrderCreated` e despacha o job

**Fluxo:**
```
POST /api/v1/orders → OrderService cria Order → evento OrderCreated
  → SendOrderNotification → SendOrderConfirmationEmail::dispatch($order)
  → API retorna 201 (sem esperar o envio)
  → queue:work processa o job em background → email enviado ao Mailtrap → log registrado
```

**Dados inclusos no email:** número do pedido, invoice (`INV-000001`), número de rastreamento (`TRK-XXXXXXXX`), itens (nome, quantidade, preço), endereços de entrega e cobrança, nome e email do cliente.

**Testar:**
```bash
# Testes automatizados
php artisan test tests/Feature/SendOrderConfirmationEmailTest.php
```
Cobre: envio bem-sucedido, dados corretos no email, processamento sem erros, múltiplos pedidos, endereço correto.

Para teste manual real, suba `php artisan serve` + `php artisan queue:work database --sleep=1`, crie um pedido pela API e confira a chegada no [Mailtrap](https://mailtrap.io).

### Configuração da Fila

```php
// config/queue.php
'default' => env('QUEUE_CONNECTION', 'database'),

'connections' => [
    'database' => [
        'driver' => 'database',
        'connection' => env('DB_QUEUE_CONNECTION'),
        'table' => env('DB_QUEUE_TABLE', 'jobs'),
        'queue' => env('DB_QUEUE', 'default'),
        'retry_after' => (int) env('DB_QUEUE_RETRY_AFTER', 90),
        'after_commit' => false,
    ],
]
```

Jobs são salvos na tabela `jobs`; `queue:work` os processa sequencialmente, com retry automático após 90 segundos em caso de falha.

---

## Decisões Arquiteturais

### Backend

1. **Service Layer** centraliza a lógica de negócio, facilitando testes e manutenção
2. **Repository Pattern** abstrai o banco, permitindo trocar a fonte de dados sem afetar os serviços
3. **DTOs** garantem tipagem entre camadas e evitam vazamento de dados
4. **Events & Listeners** desacoplam operações assíncronas da lógica principal
5. **Form Requests** centralizam e reutilizam validação
6. **Resources** padronizam a formatação das respostas JSON
7. **Soft Deletes** permitem recuperação de dados deletados
8. **Swagger/OpenAPI** mantém a documentação sincronizada com o código
9. **Ordem Stock → Payment**: a confirmação de estoque acontece antes do gateway de pagamento, evitando cobranças indevidas quando o estoque já está zerado

### Frontend

1. **TanStack Query para dados do servidor** — cache automático, stale-while-revalidate, evita requisições duplicadas (staleTime de 5min para produtos, 24h para categorias)
2. **Pinia apenas para mutações e auth** — separação clara: Query cuida da leitura, Pinia cuida da escrita e do estado de sessão
3. **Lazy loading de rotas** — reduz o bundle inicial
4. **Axios interceptors** — token injetado automaticamente; redirects de 401/403 centralizados
5. **Zod + vee-validate** — validação de formulários com inferência de tipos, sem duplicar schemas
6. **Route guards declarativos** — meta `requiresAuth`/`requiresAdmin` nas rotas, guard global único

### Performance

**Backend:** cache Redis (produtos 1h, categorias 24h), eager loading para evitar N+1, índices em colunas de busca frequente, seleção de colunas específicas quando aplicável.

**Frontend:** cache do TanStack Query entre navegações, lazy loading de páginas (code splitting via Vite), tree-shaking do Tailwind v4, proxy Vite sem overhead de CORS em dev.

**Ideias para evolução futura:** migrar filas de database para Redis em produção; considerar SSR com Nuxt 3 ou Vue SSR; rate limiting mais rigoroso; PostgreSQL em produção.

---

## CI/CD Pipeline

### Workflows

```
.github/workflows/ci.yml            # Backend: testes PHPUnit, qualidade e geração Swagger
.github/workflows/fullstack-ci.yml  # Fullstack: backend + frontend build/lint
.github/workflows/frontend-ci.yml   # Frontend: build, lint, testes e coverage
```

### Jobs do `ci.yml`

| Job | O que faz |
|-----|-----------|
| `test` | Roda PHPUnit em PHP 8.2 e 8.3, banco em memória, upload de coverage para Codecov |
| `code-quality` | `./vendor/bin/pint --test` (PSR-12) + análise estática com PHPStan |
| `swagger` | Gera `public/openapi.json`, mantendo a documentação sincronizada |
| `summary` | Consolida o resultado dos jobs; falha se algum deles falhar |

### Uso

```bash
# Push do código
git add .github/workflows/ci.yml .env.testing
git commit -m "chore: add CI/CD pipeline"
git push origin main
```

Acompanhe na aba **Actions** do GitHub.

**Rodar localmente (opcional), via [Act](https://github.com/nektos/act):**
```bash
choco install act-cli  # Windows
brew install act       # macOS

act push          # roda o workflow completo
act push -j test  # roda um job específico
```

---

## Troubleshooting

### Backend

**Servidor não inicia**
```bash
netstat -ano | findstr :8000   # verifica se a porta 8000 está em uso
php artisan serve --port=8001  # usa outra porta
```

**Erros de banco de dados**
```bash
php artisan migrate:fresh --seed   # reseta banco e seeds
php artisan migrate:status         # status das migrações
```

**Swagger não carrega**
1. Confirme que o servidor está rodando: `http://localhost:8000/api/documentation`
2. Confirme que o `openapi.json` existe: `http://localhost:8000/api/openapi.json`
3. Limpe o cache: `php artisan cache:clear`

**Token inválido**
```bash
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"password"}'
```

### Frontend

- **CORS / frontend não conecta ao backend** — confirme que o backend roda em `http://localhost:8000`; em dev, o proxy Vite redireciona `/api` automaticamente; em produção, configure `VITE_API_BASE_URL`.
- **Porta 5173 em uso** — `npm run dev -- --port 5174`
- **Erro de tipos TypeScript** — `npm run typecheck`
- **Cache do TanStack Query desatualizado:**
  ```typescript
  import { useQueryClient } from '@tanstack/vue-query';
  const queryClient = useQueryClient();
  queryClient.invalidateQueries({ queryKey: ['products'] });
  ```
- **Carrinho não atualiza após adicionar item** — `cartStore.ts` usa nova cópia de array para forçar reatividade Vue; se reaparecer, verifique se `syncItems()` é chamado após cada mutação.

---

## Exemplos de Uso

**Registrar novo usuário**
```bash
curl -X POST http://localhost:8000/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "João Silva",
    "email": "joao@example.com",
    "password": "senha123",
    "password_confirmation": "senha123"
  }'
```

**Login**
```bash
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@example.com",
    "password": "password"
  }'
```

**Listar produtos**
```bash
curl -X GET "http://localhost:8000/api/v1/products?per_page=10" \
  -H "Accept: application/json"
```

**Criar produto (admin)**
```bash
curl -X POST http://localhost:8000/api/v1/products \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer {token}" \
  -d '{
    "name": "Novo Produto",
    "slug": "novo-produto",
    "description": "Descrição",
    "price": 99.99,
    "quantity": 10,
    "category_id": 1
  }'
```

**Adicionar ao carrinho**
```bash
curl -X POST http://localhost:8000/api/v1/cart/items \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer {token}" \
  -d '{
    "product_id": 1,
    "quantity": 2
  }'
```

**Criar pedido**
```bash
curl -X POST http://localhost:8000/api/v1/orders \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer {token}" \
  -d '{
    "shipping_address": "Rua das Flores, 123",
    "billing_address": "Rua das Flores, 123",
    "notes": "Entregar com cuidado"
  }'
```

---

## Referências

| Recurso | Local |
|---------|-------|
| Swagger UI | `http://localhost:8000/api/documentation` |
| OpenAPI JSON | `http://localhost:8000/api/openapi.json` |
| Exemplos de teste (backend) | `backend/tests/Feature/` |
| Exemplos de teste (frontend) | `frontend/src/tests/` |
| Logs do backend | `backend/storage/logs/laravel.log` |
| Workflows de CI/CD | `.github/workflows/` |

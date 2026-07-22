🌐 English | [Português (BR)](PROJECT.md)

# E-Commerce Project — Technical Documentation

A fullstack e-commerce system built with **Laravel 11** (PHP 8.2) on the backend and **Vue 3 + TypeScript** on the frontend, following SOLID principles and design patterns (Repository, Service Layer, DTO).

| Layer | Stack | Tests |
|--------|-------|--------|
| Backend | Laravel 11, PHP 8.2, SQLite | 107 tests, 85.15% coverage |
| Frontend | Vue 3, TypeScript, Pinia, TanStack Query | 12 tests |
| API | 33 endpoints, Swagger/OpenAPI 3.0.0 | Interactive documentation |

**Version:** 2.0.0 · **Updated:** July 2026

---

## Table of Contents

1. [How to Run](#how-to-run)
2. [API Documentation](#api-documentation)
3. [Frontend — Vue 3 + TypeScript](#frontend--vue-3--typescript)
4. [Architecture](#architecture)
5. [Directory Structure](#directory-structure)
6. [Security & Authentication](#security--authentication)
7. [API Endpoints](#api-endpoints)
8. [Performance & Optimization](#performance--optimization)
9. [Business Flow](#business-flow)
10. [Database](#database)
11. [Testing](#testing)
12. [Best Practices & Code Standards](#best-practices--code-standards)
13. [Dependencies](#dependencies)
14. [Project Configuration](#project-configuration)
15. [Jobs & Confirmation Email](#jobs--confirmation-email)
16. [Architectural Decisions](#architectural-decisions)
17. [CI/CD Pipeline](#cicd-pipeline)
18. [Troubleshooting](#troubleshooting)
19. [Usage Examples](#usage-examples)

---

## How to Run

### Prerequisites

| Requirement | Minimum Version | Notes |
|-----------|---------------|-----------|
| PHP | 8.2+ | Tested on PHP 8.2 |
| Composer | 2.5+ | PHP dependency manager |
| Node.js | 18+ | For the frontend |
| npm | 9+ | JS package manager |
| SQLite | any | Configured by default |

### Installation (Backend + Frontend)

```bash
# 1. Clone and enter the root directory
cd ecommerce-api

# ─── BACKEND ──────────────────────────────────────────
cd backend

# 2. Install PHP dependencies
composer install

# 3. Configure environment variables
cp .env.example .env
php artisan key:generate

# 4. Run migrations and seeds
php artisan migrate:fresh --seed

# 4.1. Create the symlink for public storage
php artisan storage:link

# 5. Start the API server (Terminal 1)
php artisan serve --host=0.0.0.0 --port=8000

# Optional: process background jobs (Terminal 2)
php artisan queue:work database --sleep=1

# ─── FRONTEND ─────────────────────────────────────────
cd ../frontend

# 6. Install JS dependencies
npm install

# 7. Start the development server (Terminal 3)
npm run dev
```

### Access URLs

| Service | URL | Notes |
|---------|-----|-----------|
| Frontend (dev) | `http://localhost:5173` | Vite server |
| Backend API | `http://localhost:8000/api/v1` | Laravel |
| Swagger UI | `http://localhost:8000/api/documentation` | Interactive docs |
| OpenAPI JSON | `http://localhost:8000/api/openapi.json` | Spec for Postman/Insomnia |

### Redis via Docker (required)

The application's cache uses Redis, run via Docker in the `ecommerce-redis` container.

**First time (create the container):**
```bash
docker run -d \
  --name ecommerce-redis \
  -p 6379:6379 \
  redis:alpine
```

**Subsequent runs:**
```bash
docker start ecommerce-redis
```

**Check if it's running:**
```bash
docker ps | grep ecommerce-redis
```

> The `.env` file is already configured with `CACHE_STORE=redis`, `REDIS_HOST=127.0.0.1`, and `REDIS_PORT=6379`. No further changes are needed.

### Test Credentials

| Type | Email | Password |
|------|-------|-------|
| Admin | admin@example.com | password |
| Regular User | test@example.com | password |

---

## API Documentation

The API is documented with Swagger/OpenAPI 3.0.0.

**Interactive Interface (Swagger UI)** — `http://localhost:8000/api/documentation`
- 33 endpoints documented, directly testable via the interface
- Request/response schemas and integrated Bearer Token authentication

**JSON Specification** — `http://localhost:8000/api/openapi.json`
- Compatible with Postman, Insomnia, Swagger Editor
- Version: 1.0.0 · Base server: `http://localhost:8000/api/v1`

**Import into external tools:**
- *Postman*: File → Import → Link → paste the JSON URL
- *Insomnia*: File → Import → URL → paste the JSON URL
- *Swagger Editor* ([editor.swagger.io](https://editor.swagger.io)): File → Import URL

**Test protected endpoints:**
1. Click **"Authorize"** at the top of the Swagger UI
2. Paste a Bearer token (obtained via `/auth/login`)
3. Click **"Authorize"** again — authenticated endpoints become available

---

## Frontend — Vue 3 + TypeScript

Web interface built with Vue 3 (Composition API), TypeScript, Tailwind CSS, and TanStack Query, consuming the backend REST API with state management via Pinia.

### Technologies & Rationale

| Library | Version | Rationale |
|-----------|--------|---------------|
| Vue 3 | 3.5.38 | Composition API and better TypeScript support |
| TypeScript | ~6.0.2 | Compile-time error detection |
| Pinia | 3.0.4 | Vue 3's official state management, typed, minimal boilerplate |
| TanStack Query | 5.101.2 | Request caching, configurable staleTime, automatic invalidation |
| Vue Router | 5.1.0 | Navigation guards for authentication/admin |
| Tailwind CSS | 4.3.1 | Utility-first, small bundle with automatic purge |
| Axios | 1.18.1 | Interceptors for token and redirect on 401/403 |
| Zod | 3.25.76 | Schema validation with type inference |
| vee-validate | 4.15.1 | Form validation integrated with Zod schemas |
| Headless UI | 1.7.23 | Accessible unstyled components |
| Lucide Vue | 1.0.0 | SVG icons |
| Vite | 8.1.0 | Build tool with HMR and backend proxy |
| Vitest | 4.1.9 | Vite-compatible test runner |

### Commands

```bash
cd frontend

npm install              # install dependencies
npm run dev              # development with HMR
npm run typecheck        # TypeScript type checking
npm run lint:check       # lint (ESLint + Vue)
npm run lint             # lint with auto-fix
npm run format:check     # Prettier check
npm run format           # formatting with auto-fix
npm run build             # production build
npm run preview          # preview the build
```

### Frontend Testing

```bash
cd frontend

npm run test           # watch mode
npm run test:run       # single run (CI)
npm run test:ui        # Vitest UI graphical interface
npm run test:coverage  # with coverage
```

**Last recorded run:** 2 test files, 12 tests, ~700ms.

- `src/tests/stores/authStore.test.ts` — authentication store tests
- `src/tests/stores/cartStore.test.ts` — cart store tests

### Frontend Folder Structure

```
frontend/
├── public/                        # Public assets (favicons, etc.)
├── src/
│   ├── main.ts                    # Bootstrap: Pinia, Router, TanStack Query, mount
│   ├── App.vue                    # Root component: Header, <router-view>, Footer, Toast
│   ├── style.css                  # Global styles + Tailwind directives
│   ├── env.d.ts                   # Types for Vite environment variables
│   │
│   ├── pages/                     # 14 pages (lazy-loaded by the router)
│   │   ├── Home.vue               # Hero, featured products, stats
│   │   ├── Products.vue           # Listing with filters + pagination
│   │   ├── ProductDetail.vue      # Product detail + add to cart
│   │   ├── Cart.vue               # Cart with quantity editing (auth)
│   │   ├── Checkout.vue           # Order form with addresses (auth)
│   │   ├── Orders.vue             # Order history with pagination (auth)
│   │   ├── OrderDetail.vue        # Full order detail (auth)
│   │   ├── Profile.vue            # Profile and password editing (auth)
│   │   ├── Login.vue              # Login form
│   │   ├── Register.vue           # Registration form
│   │   ├── AdminDashboard.vue     # Admin panel: statistics, alerts (admin)
│   │   ├── AdminProducts.vue      # Product CRUD (admin)
│   │   ├── AdminCategories.vue    # Category CRUD (admin)
│   │   └── AdminOrders.vue        # Order management (admin)
│   │
│   ├── components/
│   │   ├── common/
│   │   │   ├── Header.vue         # Navbar with logo, search, auth menu, cart counter
│   │   │   ├── Footer.vue         # Footer with links and copyright
│   │   │   ├── Toast.vue          # Notifications (success/error/info/warning)
│   │   │   ├── LoadingSpinner.vue # Loading skeleton
│   │   │   └── LowStockAlert.vue  # Low/critical stock alert for admin
│   │   └── modals/
│   │       ├── BaseModal.vue          # Reusable base modal
│   │       ├── AddProductModal.vue    # Create product form
│   │       ├── EditProductModal.vue   # Edit product form
│   │       └── EditCategoryModal.vue  # Edit category form
│   │
│   ├── stores/                    # Global state with Pinia
│   │   ├── authStore.ts           # user, token, login/logout/register/updateProfile
│   │   ├── cartStore.ts           # cart, items, cart CRUD, itemCount, total
│   │   ├── productsStore.ts       # products, categories, 5min cache, CRUD (admin)
│   │   └── ordersStore.ts         # orders, 5min cache, createOrder, updateStatus
│   │
│   ├── composables/               # TanStack Query integration
│   │   ├── useProductsQuery.ts    # Product list with reactive filters
│   │   ├── useProductQuery.ts     # Individual product by ID
│   │   ├── useCategoriesQuery.ts  # Categories (24h staleTime)
│   │   ├── useOrdersQuery.ts      # User orders with filters
│   │   ├── useOrderQuery.ts       # Individual order by ID
│   │   ├── useLowStockProducts.ts # Low-stock products (admin)
│   │   └── useNotification.ts     # Global notification system
│   │
│   ├── services/
│   │   ├── api.ts                 # Axios with baseURL, interceptors (token, 401/403)
│   │   └── api-debug.ts           # Version with detailed logging for debugging
│   │
│   ├── types/                     # Product, Category, Cart, Order, User, ApiResponse, etc.
│   ├── schemas/                   # Zod schemas for form validation
│   ├── utils/                     # errorHandler.ts, validation.ts
│   ├── router/index.ts            # 14 routes, requiresAuth/requiresAdmin guards
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

### Notable Implementation Decisions

**Centralized Axios interceptors:**
```typescript
// Automatic token injection
api.interceptors.request.use(config => {
  const token = localStorage.getItem('auth_token');
  if (token) config.headers.Authorization = `Bearer ${token}`;
  return config;
});

// Automatic redirect on 401/403
api.interceptors.response.use(null, error => {
  if (error.response?.status === 401) router.push('/login');
  if (error.response?.status === 403) router.push('/');
});
```

**Declarative route guards:**
```typescript
// Route meta
meta: { requiresAuth: true, requiresAdmin: true }

// Single global guard
router.beforeEach((to) => {
  if (to.meta.requiresAuth && !authStore.isAuthenticated) → /login
  if (to.meta.requiresAdmin && !authStore.isAdmin) → /home
});
```

**Vite proxy (avoids CORS in dev):**
```typescript
// vite.config.ts
server: {
  proxy: { '/api': 'http://localhost:8000' }
}
```

### Environment Configuration

```env
# .env (frontend) - optional
VITE_API_BASE_URL=http://localhost:8000/api/v1
```

The frontend uses `http://localhost:8000/api/v1` as the default Axios baseURL.

---

## Architecture

### Fullstack Overview

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

### Backend Layers

| Layer | Location | Responsibility |
|--------|-------|-------------------|
| Presentation (Controllers) | `app/Http/Controllers/Api/V1/` | Receives HTTP requests, formats responses |
| Service (business logic) | `app/Services/` | Coordinates operations and validations — `ProductService`, `CartService`, `OrderService`, `CategoryService`, `ImageService` |
| Repository (data access) | `app/Repositories/` | Database abstraction — `ProductRepository`, `CategoryRepository`, `OrderRepository`, `CartRepository` |
| Model | `app/Models/` | Data structure and Eloquent relationships |

### Design Patterns

**Backend:** Repository Pattern, Service Layer, DTOs, Factory (tests), Observer (Events/Listeners), Job Pattern (queues).

**Frontend:** Composable Pattern (TanStack Query), Store Pattern (Pinia), Presenter/Container (pages as containers, components as presenters), Guard Pattern (routes).

---

## Directory Structure

```
ecommerce-api/
├── backend/                           # Laravel 11 API
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/Api/V1/   # API v1 controllers
│   │   │   ├── Middleware/           # AdminMiddleware, HandleCors
│   │   │   ├── Requests/             # Validation Form Requests
│   │   │   └── Resources/            # Resources for JSON formatting
│   │   ├── Models/                   # Eloquent models
│   │   ├── Services/                 # Service layer (+ ImageService)
│   │   ├── Repositories/             # Repository layer
│   │   ├── DTOs/                     # Data Transfer Objects
│   │   ├── Events/                   # Application events
│   │   ├── Listeners/                # Event listeners
│   │   ├── Jobs/                     # Queue jobs
│   │   ├── Rules/                    # Custom validation rules
│   │   ├── Policies/                 # Authorization policies
│   │   └── Traits/                   # Reusable traits (ApiResponses)
│   ├── database/
│   │   ├── migrations/               # Database migrations
│   │   ├── factories/                # Test factories
│   │   └── seeders/                  # Data seeders
│   ├── routes/
│   │   ├── api.php                   # API routes
│   │   └── web.php                   # Web routes
│   ├── tests/                        # Automated tests
│   └── config/                       # Configuration files
│
└── frontend/                          # Vue 3 + TypeScript SPA
    └── src/
        ├── pages/                     # 14 pages (lazy-loaded)
        ├── components/                # Reusable components
        ├── stores/                    # Global state (Pinia)
        ├── composables/               # TanStack Query integration
        ├── services/                  # Axios API client
        ├── types/                     # TypeScript types
        ├── schemas/                   # Zod schemas
        ├── utils/                     # Utilities
        ├── router/                    # Vue Router + guards
        └── tests/                     # Vitest tests
```

---

## Security & Authentication

### Authentication (Laravel Sanctum)

- **Method:** Personal Access Tokens · **Type:** Bearer Token · **Header:** `Authorization: Bearer {token}`
- **Public endpoints:** `/auth/register`, `/auth/login`
- **Protected endpoints:** `auth:sanctum` middleware

**Flow:**
1. `POST /auth/register` — registers a new user
2. `POST /auth/login` — returns a token (plain text)
3. Token used in the `Authorization` header
4. `POST /auth/logout` — revokes the token
5. `GET /auth/me` — returns the authenticated user's data

### Authorization

- **AdminMiddleware** checks `is_admin = true`; applied to create/update/delete routes. Returns `403 Forbidden` without permission.
- **Products and Categories:** only admins can `POST`, `PUT`, `DELETE`.
- **Orders:** only admins can update status.
- **Cart:** only the owner can access it.
- **Policies** cover additional granular authorization.

### Validation

**Form Requests:** `StoreProductRequest`, `UpdateProductRequest`, `StoreCategoryRequest`, `CreateOrderRequest`, `AddToCartRequest`.

**Custom rules:** `UniqueSlug` (unique slug per category), price/quantity validations, stock availability checks.

### Other Protections

- **CORS** enabled via `HandleCors` middleware
- **Rate limiting** applied to API endpoints
- **HTTPS** recommended in production
- **Token expiration** configurable via Sanctum

---

## API Endpoints

Base URL: `http://localhost:8000/api/v1` — 33 endpoints total.

### Authentication (5)
```
POST   /auth/register           # Register new user
POST   /auth/login              # Log in
POST   /auth/logout             # Logout (authenticated)
GET    /auth/me                 # User data (authenticated)
PUT    /auth/profile            # Update profile (authenticated)
```

### Products (9)
```
GET    /products                # List with filters (public)
GET    /products/{id}           # Get details (public)
GET    /products/low-stock      # Low stock (admin)
POST   /products                # Create (admin)
PUT    /products/{id}           # Update (admin)
POST   /products/{id}           # Update via FormData with _method: PUT (admin)
DELETE /products/{id}           # Delete (admin)
POST   /products/{id}/image     # Upload image (admin)
DELETE /products/{id}/image     # Remove image (admin)
```

**Available filters on `GET /products`:** `category_id`, `search`, `min_price`, `max_price`, `sort`, `per_page`.

### Categories (6)
```
GET    /categories              # List with hierarchy (public)
GET    /categories/{id}         # Get category (public)
GET    /categories/{id}/products # Category products (public)
POST   /categories              # Create (admin)
PUT    /categories/{id}         # Update (admin)
DELETE /categories/{id}         # Delete (admin)
```

### Cart (5 — authenticated)
```
GET    /cart                    # Get cart
POST   /cart/items              # Add item
PUT    /cart/items/{itemId}     # Update quantity
DELETE /cart/items/{itemId}     # Remove item
DELETE /cart                    # Clear cart
```

### Orders (4 — authenticated)
```
GET    /orders                  # List user orders
GET    /orders/{id}             # Get order details
POST   /orders                  # Create order
PUT    /orders/{id}/status      # Update status (admin)
```

**Valid statuses:** `pending`, `processing`, `shipped`, `delivered`, `cancelled`

---

## Performance & Optimization

### API Resources

`ProductResource`, `CategoryResource`, `CartResource`, `CartItemResource`, `OrderResource`, `OrderItemResource` — hide sensitive data (hashes, internal timestamps), convert types (Carbon → ISO 8601), and automatically load relationships.

### Query Optimization

- **Eager loading** (`with('items.product')`) avoids the N+1 query problem.
- **Database indexes:** `products` (category_id, created_at), `orders` (user_id, created_at, status), `cart_items` (cart_id), `stock_movements` (product_id, type).
- **TTL cache via Redis:** products for 1 hour (invalidated on update), categories for 24 hours, with tagged cache for selective invalidation.

### Structured Logging

Structured JSON logging on key events: `ProductCreated`, `OrderCreated`, `OrderItemCreated`, `StockMovement`, `SendOrderConfirmationEmail`.

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

## Business Flow

### Order Creation

1. User adds items to the cart
2. User checks out, creating an order
3. System validates stock availability
4. Creates an order record in the database
5. Creates order items
6. Updates stock quantity
7. Logs stock movement
8. Clears the user's cart
9. Fires the `OrderCreated` event
10. Listener sends confirmation email (async via queue)

### Stock Control

- `Product::lowStock()` — scope for low-stock products
- `Product::inStock()` — scope for products with available stock
- `StockMovement` — tracks all stock changes (inbound, outbound, adjustment, sale, return)

---

## Database

### Main Models

- **Product** — has one Category, belongsToMany Tags, hasMany OrderItems, hasMany StockMovements
- **Order** — belongsTo User, hasMany OrderItems
- **Cart** — belongsTo User, hasMany CartItems

### Relationships

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

## Testing

### Backend — PHPUnit

Last run: **107 tests, 186 assertions, 100% success.** Coverage: 85.15% lines (814/956), 66.34% methods (136/205), 50.00% classes (30/60).

```bash
cd backend
php artisan test              # all tests
./vendor/bin/phpunit          # directly via PHPUnit
php artisan test --compact    # fast run, no coverage
```

**Coverage by area:**
- Authentication (5) — register, login, logout, profile, validations
- Products (5+) — CRUD, filters, search, validations
- Categories (6) — CRUD, hierarchy, relationships
- Cart (4) — add, update, remove, clear
- Orders (8) — create, list, update status, validations, permissions
- Confirmation email (3) — sending, correct data, error handling
- Order processing (4) — success, no user, no items, invalid total
- Stock update (6) — success, StockMovement, insufficient stock, multiple items, StockLow event
- Listeners/events (5) — SendOrderNotification, LogProductCreation, NotifyAdminLowStock
- Repositories (11) — CartRepository, CategoryRepository, ProductRepository, OrderRepository
- Unit: ProductService (4), CartService (4)

**HTML coverage** (requires Xdebug):
```bash
./vendor/bin/phpunit --coverage-html=coverage-report
open coverage-report/index.html    # macOS/Linux
start coverage-report/index.html   # Windows
```

**Text coverage:**
```bash
./vendor/bin/phpunit --coverage-text
```

### Frontend — Vitest

Last run: **12 passing tests** across 2 files.

```bash
cd frontend
npm run test           # watch mode
npm run test:run       # single run (CI)
npm run test:ui        # graphical interface
npm run test:coverage  # with coverage
```

| File | Tests | Covers |
|---------|--------|-----------|
| `src/tests/stores/authStore.test.ts` | 6 | Login, logout, register, isAdmin, token persistence |
| `src/tests/stores/cartStore.test.ts` | 6 | Add item, remove, update quantity, total, clear |

```typescript
// vitest.config.ts
{
  environment: 'happy-dom',
  coverage: { provider: 'v8' }
}
```

---

## Best Practices & Code Standards

### PHP — PSR-12 with Laravel Pint

```bash
./vendor/bin/pint --test        # check compliance
./vendor/bin/pint               # auto-fix
./vendor/bin/pint --test app/Models/User.php   # specific file
./vendor/bin/pint --verbose     # verbose mode
```

**Applied standards:** 4-space indentation, 120-character max line length, organized namespaces and use statements, camelCase for methods and snake_case for variables.

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

It's recommended to add `./vendor/bin/pint --test` to the CI pipeline, blocking deployment on failure.

### JavaScript / Frontend

- **ESLint** (`eslint.config.js`) with Vue 3 and TypeScript plugins
- **Prettier** for automatic formatting of `.ts`, `.vue`, and `.json`

```bash
npm run lint:check      # check
npm run format:check    # check
npm run lint            # fix
npm run format          # fix
```

---

## Dependencies

### Backend (PHP / Laravel)

**Core:** Laravel 11, Laravel Sanctum 4.3.2 (authentication via Personal Access Tokens), PHP 8.2+

**Documentation/API:** L5-Swagger 11.1.0, Zircote/Swagger-PHP 6.2.0

**Dev & Quality:** PHPUnit 11.5.50, Laravel Tinker 2.10.1, Laravel Pint 1.24, Faker, Collision 8.6, SQLite, Redis (via predis), queue via Database Queue Driver

### Frontend (JavaScript / Vue)

**Core:** Vue 3.5.38, TypeScript ~6.0.2, Vite 8.1.0

**State & Data Fetching:** Pinia 3.0.4, @tanstack/vue-query 5.101.2

**Routing & HTTP:** vue-router 5.1.0, axios 1.18.1

**Forms:** vee-validate 4.15.1, @vee-validate/zod 4.15.1, zod 3.25.76

**UI:** Tailwind CSS 4.3.1, @headlessui/vue 1.7.23, lucide-vue-next 1.0.0

**Testing (dev):** Vitest 4.1.9, @testing-library/vue 8.1.0, happy-dom 20.10.6, @vitest/coverage-v8

**Code quality (dev):** ESLint 10.5.0, eslint-plugin-vue, typescript-eslint, Prettier 3.8.5, vue-tsc 3.3.5

---

## Project Configuration

### `.env` (default)

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
- **Products TTL:** 1 hour · **Categories TTL:** 24 hours
- **Invalidation:** automatic on data update/delete

### Queues

- **Driver:** Database (`jobs` table)
- **Process:** `php artisan queue:work database --sleep=1`

---

## Jobs & Confirmation Email

### Implemented Jobs

1. **UpdateStockAfterOrder** — triggered when an order is created; updates stock, creates a `StockMovement` for auditing, fires the `StockLow` event if needed; uses transactions and fails (with rollback) if stock is insufficient.
2. **ProcessOrder** — payment validation (mocked), gateway integration (placeholder), stock confirmation; structured success/error logging.
3. **SendOrderConfirmationEmail** — sends confirmation email to the customer; logs delivery success/error.

The `SendOrderNotification` listener triggers all three jobs when the `OrderCreated` event fires, in the background, without blocking the HTTP response.

```bash
# Terminal 1: start the queue
php artisan queue:work database

# Terminal 2: create an order via the API
curl -X POST http://localhost:8000/api/v1/orders \
  -H "Authorization: Bearer {token}" \
  -d "..."

# The 3 jobs are processed automatically in Terminal 1
```

### Order Confirmation Email

Configured to send via **Mailtrap** (SMTP sandbox for development):

```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_token_here
MAIL_PASSWORD=your_token_here
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@ecommerce.com
MAIL_FROM_NAME="My E-Commerce"
```

**Components:**
- `app/Mail/OrderConfirmation.php` — Mailable with order data (number, items, addresses, tracking); template at `resources/views/emails/order-confirmation.blade.php`
- `app/Jobs/SendOrderConfirmationEmail.php` — asynchronous sending via `ShouldQueue`, with validation, logging, and automatic retry
- `app/Listeners/SendOrderNotification.php` — listens for `OrderCreated` and dispatches the job

**Flow:**
```
POST /api/v1/orders → OrderService creates Order → OrderCreated event
  → SendOrderNotification → SendOrderConfirmationEmail::dispatch($order)
  → API returns 201 (without waiting for delivery)
  → queue:work processes the job in the background → email sent to Mailtrap → log recorded
```

**Data included in the email:** order number, invoice (`INV-000001`), tracking number (`TRK-XXXXXXXX`), items (name, quantity, price), shipping and billing addresses, customer name and email.

**Test:**
```bash
# Automated tests
php artisan test tests/Feature/SendOrderConfirmationEmailTest.php
```
Covers: successful sending, correct email data, error-free processing, multiple orders, correct address.

For real manual testing, start `php artisan serve` + `php artisan queue:work database --sleep=1`, create an order via the API, and check delivery in [Mailtrap](https://mailtrap.io).

### Queue Configuration

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

Jobs are saved to the `jobs` table; `queue:work` processes them sequentially, with automatic retry after 90 seconds on failure.

---

## Architectural Decisions

### Backend

1. **Service Layer** centralizes business logic, easing testing and maintenance
2. **Repository Pattern** abstracts the database, allowing the data source to be swapped without affecting services
3. **DTOs** ensure typing between layers and prevent data leakage
4. **Events & Listeners** decouple asynchronous operations from the main logic
5. **Form Requests** centralize and reuse validation
6. **Resources** standardize JSON response formatting
7. **Soft Deletes** allow recovery of deleted data
8. **Swagger/OpenAPI** keeps documentation in sync with the code
9. **Stock → Payment order**: stock confirmation happens before the payment gateway, avoiding improper charges when stock is already depleted

### Frontend

1. **TanStack Query for server data** — automatic caching, stale-while-revalidate, avoids duplicate requests (5min staleTime for products, 24h for categories)
2. **Pinia only for mutations and auth** — clear separation: Query handles reads, Pinia handles writes and session state
3. **Lazy-loaded routes** — reduces the initial bundle
4. **Axios interceptors** — token injected automatically; 401/403 redirects centralized
5. **Zod + vee-validate** — form validation with type inference, without duplicating schemas
6. **Declarative route guards** — `requiresAuth`/`requiresAdmin` meta on routes, single global guard

### Performance

**Backend:** Redis cache (products 1h, categories 24h), eager loading to avoid N+1, indexes on frequently searched columns, specific column selection where applicable.

**Frontend:** TanStack Query cache across navigations, lazy-loaded pages (code splitting via Vite), Tailwind v4 tree-shaking, Vite proxy without CORS overhead in dev.

**Ideas for future evolution:** migrate queues from database to Redis in production; consider SSR with Nuxt 3 or Vue SSR; stricter rate limiting; PostgreSQL in production.

---

## CI/CD Pipeline

### Workflows

```
.github/workflows/ci.yml            # Backend: PHPUnit tests, quality checks, Swagger generation
.github/workflows/fullstack-ci.yml  # Fullstack: backend + frontend build/lint
.github/workflows/frontend-ci.yml   # Frontend: build, lint, tests, and coverage
```

### `ci.yml` Jobs

| Job | What it does |
|-----|-----------|
| `test` | Runs PHPUnit on PHP 8.2 and 8.3, in-memory database, uploads coverage to Codecov |
| `code-quality` | `./vendor/bin/pint --test` (PSR-12) + static analysis with PHPStan |
| `swagger` | Generates `public/openapi.json`, keeping documentation in sync |
| `summary` | Consolidates job results; fails if any of them fail |

### Usage

```bash
# Push the code
git add .github/workflows/ci.yml .env.testing
git commit -m "chore: add CI/CD pipeline"
git push origin main
```

Track progress in the GitHub **Actions** tab.

**Run locally (optional), via [Act](https://github.com/nektos/act):**
```bash
choco install act-cli  # Windows
brew install act       # macOS

act push          # runs the full workflow
act push -j test  # runs a specific job
```

---

## Troubleshooting

### Backend

**Server won't start**
```bash
netstat -ano | findstr :8000   # check if port 8000 is in use
php artisan serve --port=8001  # use another port
```

**Database errors**
```bash
php artisan migrate:fresh --seed   # reset database and seeds
php artisan migrate:status         # migration status
```

**Swagger won't load**
1. Confirm the server is running: `http://localhost:8000/api/documentation`
2. Confirm `openapi.json` exists: `http://localhost:8000/api/openapi.json`
3. Clear the cache: `php artisan cache:clear`

**Invalid token**
```bash
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"password"}'
```

### Frontend

- **CORS / frontend can't connect to backend** — confirm the backend is running at `http://localhost:8000`; in dev, the Vite proxy redirects `/api` automatically; in production, configure `VITE_API_BASE_URL`.
- **Port 5173 in use** — `npm run dev -- --port 5174`
- **TypeScript type errors** — `npm run typecheck`
- **Stale TanStack Query cache:**
  ```typescript
  import { useQueryClient } from '@tanstack/vue-query';
  const queryClient = useQueryClient();
  queryClient.invalidateQueries({ queryKey: ['products'] });
  ```
- **Cart doesn't update after adding an item** — `cartStore.ts` uses a new array copy to force Vue reactivity; if this recurs, check that `syncItems()` is called after each mutation.

---

## Usage Examples

**Register a new user**
```bash
curl -X POST http://localhost:8000/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Smith",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
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

**List products**
```bash
curl -X GET "http://localhost:8000/api/v1/products?per_page=10" \
  -H "Accept: application/json"
```

**Create product (admin)**
```bash
curl -X POST http://localhost:8000/api/v1/products \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer {token}" \
  -d '{
    "name": "New Product",
    "slug": "new-product",
    "description": "Description",
    "price": 99.99,
    "quantity": 10,
    "category_id": 1
  }'
```

**Add to cart**
```bash
curl -X POST http://localhost:8000/api/v1/cart/items \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer {token}" \
  -d '{
    "product_id": 1,
    "quantity": 2
  }'
```

**Create order**
```bash
curl -X POST http://localhost:8000/api/v1/orders \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer {token}" \
  -d '{
    "shipping_address": "123 Flower Street",
    "billing_address": "123 Flower Street",
    "notes": "Handle with care"
  }'
```

---

## References

| Resource | Location |
|---------|-------|
| Swagger UI | `http://localhost:8000/api/documentation` |
| OpenAPI JSON | `http://localhost:8000/api/openapi.json` |
| Test examples (backend) | `backend/tests/Feature/` |
| Test examples (frontend) | `frontend/src/tests/` |
| Backend logs | `backend/storage/logs/laravel.log` |
| CI/CD workflows | `.github/workflows/` |
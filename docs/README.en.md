# E-Commerce Fullstack

## About the project

A fullstack application that simulates a complete e-commerce flow — from browsing and filtering products to checkout with stock control and order-confirmation email sent via a queue. The backend exposes a REST API with 33 endpoints, and the frontend consumes that API through a Vue 3 SPA, with an admin panel separated by user role.

🔗 **Live demo**: [ecommerce-projeto.duckdns.org](https://ecommerce-projeto.duckdns.org)

## Features

- User registration and authentication via JWT (Laravel Sanctum)
- Product listing with filters by category, price range, and text search
- Shopping cart persisted per authenticated user
- Checkout with real-time stock validation and movement logging
- Order history and detail view with status tracking
- Admin panel for managing products, categories, and orders
- Product image upload and removal
- Low-stock alert in the admin panel
- Order confirmation email processed asynchronously via a queue
- Interactive API documentation with Swagger UI

## Technologies used

**Language**
- PHP 8.2
- TypeScript

**Framework / Library**
- Laravel 11
- Vue 3 (Composition API)
- Pinia
- TanStack Query v5
- Vue Router 5
- Axios
- vee-validate + Zod

**Database**
- SQLite (default) / MySQL (optional)

**Cache**
- Redis (tagged cache with selective invalidation)

**Styling**
- Tailwind CSS 4
- Headless UI
- Lucide Vue

**Testing**
- PHPUnit (107 tests — 85.15% line coverage)
- Vitest + happy-dom (12 tests)

**Tools**
- Vite 8 (build + dev proxy)
- Laravel Pint (PSR-12)
- ESLint + Prettier
- GitHub Actions (CI/CD)
- Docker (Redis)

**Other technical features**
- Laravel Sanctum (Bearer Token)
- Laravel Events, Listeners, and Jobs (async queue)
- API Resources (response formatting and filtering)
- Form Requests with custom rules (`UniqueSlug`, `SufficientStock`)
- Swagger / OpenAPI 3.0.0

## How to run the project

### Prerequisites

- PHP 8.2+
- Composer 2.5+
- Node.js 18+ and npm 9+
- Docker (for the Redis container)

### Backend

```bash
cd backend

composer install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
php artisan storage:link

# Terminal 1 — API server
php artisan serve --host=0.0.0.0 --port=8000

# Terminal 2 — queue worker (optional, required for emails)
php artisan queue:work database --sleep=1
```

### Redis

```bash
# First time
docker run -d --name ecommerce-redis -p 6379:6379 redis:alpine

# Subsequent runs
docker start ecommerce-redis
```

### Frontend

```bash
cd frontend

npm install

# Terminal 3 — dev server
npm run dev
```

### Access URLs

| Service | URL |
|---------|-----|
| Frontend | `http://localhost:5173` |
| API | `http://localhost:8000/api/v1` |
| Swagger UI | `http://localhost:8000/api/documentation` |

### Test credentials

| Role | Email | Password |
|--------|--------|-------|
| Admin | admin@example.com | password |
| User | test@example.com | password |

## Project structure

```
challenge-fullstack-pleno/
├── backend/          # Laravel 11 — REST API
│   └── app/
│       ├── Http/Controllers/Api/V1/  # Receive requests and delegate
│       ├── Services/                 # Business logic
│       ├── Repositories/             # Database access abstraction
│       ├── DTOs/                     # Data transfer between layers
│       ├── Models/                   # Eloquent models and relationships
│       ├── Events/ + Listeners/      # Event flow (order created, low stock)
│       ├── Jobs/                     # Async tasks (email, stock)
│       ├── Rules/                    # Custom validation rules
│       ├── Policies/                 # Resource-based authorization
│       └── Http/Resources/           # JSON response formatting and filtering
│
└── frontend/         # Vue 3 + TypeScript — SPA
    └── src/
        ├── pages/        # 14 lazy-loaded pages (public, auth, and admin)
        ├── components/   # Reusable components and modals
        ├── stores/       # Global state with Pinia (auth, cart, products, orders)
        ├── composables/  # TanStack Query integration (caching and invalidation)
        ├── services/     # Axios client with token and redirect interceptors
        ├── schemas/      # Form validation with Zod
        └── router/       # Routes with requiresAuth and requiresAdmin guards
```

The backend follows a Controller → Service → Repository → Model layered architecture. The frontend separates state (Pinia), data fetching (TanStack Query), and presentation (pages + components), with form validation via Zod.

## What this project demonstrates

- REST API structuring with versioning, resources, and OpenAPI documentation
- Application of the Repository + Service Layer pattern in PHP, favoring testability and separation of concerns
- Role-based authentication and authorization (admin / user) with Laravel Sanctum and Policies
- Use of queues and events to decouple asynchronous operations (email sending, stock updates)
- Distributed caching with Redis and selective invalidation by tags
- Reactive state management on the frontend with Pinia and TanStack Query
- Schema validation in TypeScript with Zod integrated with vee-validate
- Automated testing on the backend (PHPUnit, 85% coverage) and frontend (Vitest)
- CI/CD pipeline setup with GitHub Actions

## Future improvements

- Add refresh token to extend sessions without requiring a new login
- Implement pagination in the admin orders panel
- Expand frontend test coverage for the main pages
- Add support for multiple images per product
- Create a sales-report-by-period endpoint for the admin panel

## Author
 
**João Pereira**
 
[![LinkedIn](https://img.shields.io/badge/LinkedIn-devjoaopereira-0A66C2?logo=linkedin&logoColor=white)](https://www.linkedin.com/in/devjoaopereira/)
[![GitHub](https://img.shields.io/badge/GitHub-DevJoaoVitorBP-181717?logo=github&logoColor=white)](https://github.com/DevJoaoVitorBP)
[![E-mail](https://img.shields.io/badge/E--mail-developerjoaopereira%40gmail.com-D14836?logo=gmail&logoColor=white)](mailto:developerjoaopereira@gmail.com)
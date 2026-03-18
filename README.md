# Logistics CRM — Enterprise Freight Management SaaS

[![Laravel 12](https://img.shields.io/badge/Laravel-12.x-red.svg)](https://laravel.com)
[![Vue 3](https://img.shields.io/badge/Vue-3.x-emerald.svg)](https://vuejs.org)
[![Inertia.js v2](https://img.shields.io/badge/Inertia.js-v2.0-blue.svg)](https://inertiajs.com)
[![Pest Testing](https://img.shields.io/badge/Pest-v3.0-pink.svg)](https://pestphp.com)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

A high-performance B2B Logistics CRM designed for freight companies and their clients. Built with a focus on **concurrency safety**, **strict multi-tenancy**, and **automated logistics workflows**.

---

## 🚀 Key Features

- **Real-time Synchronization:** Built-in **Laravel Reverb** WebSocket server provides instant UI updates for order statuses and background PDF generation without page reloads.
- **Advanced Pricing Engine:** Implemented via the **Pipeline Pattern**. Calculates base rates, ADR surcharges, insurance fees, and volume discounts.
- **Strict Multi-tenancy:** Data isolation between logistics providers and client companies using Eloquent Global Scopes.
- **Concurrency Protection:** Uses **Pessimistic Locking** (`lockForUpdate`) to prevent race conditions during truck assignment and status changes.
- **Inertia v2 Architecture:** Leverages **Deferred Props** for instant dashboard loading, **Partial Reloads** for WebSocket updates, and **Wayfinder** for typed routing.
- **GIS Integration:** Real-time distance and transit time calculations via OSRM API with automatic Redis caching and Haversine fallback.
- **Automated Document Flow:** Background generation of **CMR** and **Invoices** (PDF) using Laravel Queues and MinIO (S3) storage.
- **Role-Based Access Control (RBAC):** Granular permissions for Admins, Managers, and Observers via Spatie Permissions.

---

## 🛠️ Tech Stack

- **Backend:** PHP 8.5+, Laravel 12, Octane (FrankenPHP), Reverb (WebSockets).
- **Frontend:** Vue 3 (Composition API), Inertia.js v2, Tailwind CSS v4.
- **Database:** PostgreSQL 16, Redis (Caching/Sessions/Queues).
- **Storage:** MinIO / AWS S3 (Documents & Assets).
- **Testing:** Pest (Feature & Unit tests, 100% Core Logic Coverage).
- **DevOps:** Docker (Sail/Multi-stage), GitHub Actions (CI/CD), Nginx Proxy Manager.

---

## 📖 Documentation

Detailed technical documentation is available in the [`/readme/en`](./readme/en) directory:

1. [Architecture & Patterns](./readme/en/architecture_and_patterns.md) — Deep dive into Actions, Pipelines, and Locking.
2. [Business Logic & RBAC](./readme/en/business_logic.md) — Roles, permissions, and multi-tenancy.
3. [Database Schema](./readme/en/database_schema.md) — Entity relationships and optimization.
4. [Frontend & UI/UX](./readme/en/frontend_and_ui.md) — Vue 3 patterns and GIS integration.
5. [Infrastructure & Deployment](./readme/en/infrastructure_and_deployment.md) — Docker, Octane, and CI/CD setup.
6. [Testing Architecture](./readme/en/testing.md) — Strategy for 100% reliable business logic.

---

## ⚙️ Quick Start (Local Development)

### 1. Clone the repository

```bash
git clone https://github.com/lisovyi3441/logistics-crm.git
cd logistics-crm
```

### 2. Setup with Laravel Sail

```bash
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php85-composer:latest \
    composer install --ignore-platform-reqs

cp .env.example .env
./vendor/bin/sail up -d
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate --seed

# Ensure MinIO bucket exists for documents
./vendor/bin/sail shell -c "mc alias set local http://minio:9000 sail password && mc mb local/local || true"

./vendor/bin/sail npm install
./vendor/bin/sail npm run dev
```

### 3. Demo Access

Visit `http://localhost` and use the **1-Click Login** buttons on the Welcome page:

- **Admin:** `admin@gmail.com` / `password`
- **Manager:** `manager@gmail.com` / `password`

---

## ✅ Quality Standards

The project maintains high code quality standards:

- **Static Analysis:** PHPStan/Larastan (Level 5 - Professional Standard).
- **Formatting:** Laravel Pint (Strict preset).
- **Testing:** Automated CI/CD pipeline on every push.

---

## 📄 License

The MIT License (MIT). Please see [License File](LICENSE) for more information.

Developed with ❤️ by **Maksym Lisovyi**.

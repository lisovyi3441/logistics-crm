# Logistics CRM - B2B SaaS for Freight Management

[![PHP Version](https://img.shields.io/badge/PHP-8.4-777BB4.svg?logo=php&logoColor=white)](https://php.net/)
[![Laravel Version](https://img.shields.io/badge/Laravel-12.x-FF2D20.svg?logo=laravel&logoColor=white)](https://laravel.com)
[![Vue.js](https://img.shields.io/badge/Vue.js-3.x-4FC08D.svg?logo=vuedotjs&logoColor=white)](https://vuejs.org/)
[![Pest PHP](https://img.shields.io/badge/Pest-Coverage_100%25-FF2D20.svg)](https://pestphp.com)
[![License](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE.md)

**Logistics CRM** is an advanced, enterprise-grade B2B platform designed for freight and logistics management. It demonstrates modern architectural patterns, strict domain logic isolation, and high-performance infrastructure setup.

## 🚀 Live Demo

**URL:** [crm.lisovyi.me](https://crm.lisovyi.me)

### Test Accounts:

The database is pre-seeded with role-specific accounts. You can use the 1-click login on the Welcome page or use these credentials (Password for all: `password`):

- **Admin** (Full Access): `admin@gmail.com`
- **Manager** (Company Scoped): `manager@gmail.com`
- **Observer** (Read-Only): `observer@gmail.com`

---

## 🏗 Requirements & Tech Stack

This project was built focusing on **Engineering Excellence**, moving past standard CRUD operations to tackle complex business logic, strict validation, and global infrastructure deployment.

- **Core:** PHP 8.4, Laravel 12
- **Performance:** Laravel Octane (FrankenPHP), Redis
- **Architecture:** Pipeline Pattern (Pricing Engine), Service Contracts, Multi-tenant Data Isolation
- **Access Control:** Spatie Laravel Permission (Declarative RBAC via Enums)
- **Testing:** Pest PHP (100% coverage of core business rules)
- **Frontend:** Vue 3 (Composition API), Inertia.js v2, Tailwind CSS v4, Radix Vue
- **Infrastructure:** Docker, Nginx Proxy Manager, MinIO (S3-compatible storage), DigitalOcean
- **External APIs:** OSRM (Open Source Routing Machine)

---

## 🌟 Key Architectural Features

1.  **Pricing Engine via Pipeline Pattern**
    Order pricing is decoupled into isolated, testable pipeline stages (Base Price, ADR Surcharges, Insurance, Taxes). This allows dynamic cost calculations based on vehicle limits and distances without cluttering the models.
2.  **Transactional Integrity**
    All critical business operations (such as assigning a truck or changing order statuses) are strictly wrapped in Database Transactions to prevent data race conditions.
3.  **Strict Domain Validation & Data Isolation**
    The system employs multi-tenancy. Managers and Observers can only interact with entities that belong to their specific `company_id`. Global scopes and Gates guarantee data safety.
4.  **Automated Document Generation (Jobs & S3)**
    CMRs and Invoices are generated asynchronously via Laravel Queue Jobs (leveraging `DOMPDF`). Documents are securely stored in S3-compatible object storage (MinIO) and retrieved via localized short-lived URLs.
5.  **Geospatial Routing (GIS Integration)**
    Orders calculate real transit times and distances via integration with the OSRM routing engine API, cached via Redis to prevent rate-limiting on high-load operations.

---

## � Local Development (Laravel Sail)

The easiest way to get started is by using Laravel Sail, a light-weight command-line interface for interacting with Laravel's default Docker development environment.

### 1. Clone & Setup

```bash
git clone https://github.com/lisovyi3441/logistics-crm.git
cd logistics-crm
cp .env.example .env
```

### 2. Install Dependencies

If you don't have PHP installed locally, you can use a small Docker container to install the Composer dependencies:

```bash
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php84-composer:latest \
    composer install --ignore-platform-reqs
```

### 3. Start Containers

```bash
./vendor/bin/sail up -d
```

### 4. Initialize Database & Frontend

```bash
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate:fresh --seed
./vendor/bin/sail npm install
./vendor/bin/sail npm run dev
```

Your application should now be accessible at `http://localhost`.

---

## ✅ Testing & Code Quality

The project maintains **100% test coverage** for all critical business actions and policy enforcement using Pest PHP.

**Run the Test Suite:**

```bash
./vendor/bin/sail artisan test --compact
```

**Run Code Formatter (Laravel Pint):**

```bash
./vendor/bin/sail bin pint
```

---

_This project is built and maintained by **Maksym Lisovyi**._
_Portfolio: [lisovyi.me](https://lisovyi.me) | LinkedIn: [Maksym Lisovyi](https://www.linkedin.com/in/marl-lis/)_

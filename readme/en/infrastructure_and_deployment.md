# Infrastructure & Deployment

The project is architected for both local development and high-performance production environments.

## 🛠️ 1. Local Development (Laravel Sail)

Uses **Docker Compose** with the following services:
- **`laravel.test`**: PHP 8.5 application container.
- **`mysql:8.4`**: Database.
- **`redis:alpine`**: Caching and sessions.
- **`minio`**: Local S3-compatible storage for documents.
- **`mailpit`**: SMTP testing tool.

## 🚀 2. Production (FrankenPHP & Octane)

Optimized for high concurrency and low latency.
- **Server**: **FrankenPHP** with **Laravel Octane**. The framework is loaded into RAM once, serving requests in milliseconds.
- **Stateless Architecture**: The application is fully stateless, making it horizontally scalable (ready for K8s/Docker Swarm).
- **Asset Pipeline**: Multi-stage Docker build for minimal image size.

## ⚙️ 3. CI/CD (GitHub Actions)
- **`tests.yml`**: Runs the Pest test suite on every push.
- **`lint.yml`**: Ensures code style consistency (Pint/Prettier).
- **`deploy.yml`**: Automated deployment to production via SSH.

## 📡 4. Monitoring
- **Laravel Telescope**: Deep monitoring of requests, jobs, and SQL queries (Local only).
- **Health Checks**: Integrated Docker health checks for service reliability.

# Infrastructure & Deployment

The project is architected for both local development and high-performance production environments.

## 🛠️ 1. Local Development (Laravel Sail)

Uses **Docker Compose** with the following services:

- **`laravel.test`**: PHP 8.5 application container.
- **`pgsql`**: Database (PostgreSQL 16).
- **`redis:alpine`**: Caching and sessions.
- **`minio`**: Local S3-compatible storage for documents.
- **`mailpit`**: SMTP testing tool.

## 🚀 2. Production (FrankenPHP, Octane & Reverb)

Optimized for high concurrency and low latency.

- **Server**: **FrankenPHP** with **Laravel Octane**. The framework is loaded into RAM once, serving requests in milliseconds.
- **WebSockets**: A dedicated **Laravel Reverb** container handles real-time connections, isolated from the main web server to ensure stability.
- **Stateless Architecture**: The application is fully stateless, making it horizontally scalable (ready for K8s/Docker Swarm).
- **Asset Pipeline**: Multi-stage Docker build for minimal image size.
- **Security**: Uses strict CIDR blocks for `TRUSTED_PROXIES` behind Nginx Proxy Manager to prevent IP spoofing.

## ⚙️ 3. CI/CD (GitHub Actions)

- **`deploy.yml`**: A unified pipeline that runs the Pest test suite, ensures code style consistency (Pint/Prettier), and performs automated zero-downtime deployment to production via SSH.

## 📡 4. Monitoring

- **Laravel Telescope**: Deep monitoring of requests, jobs, and SQL queries (Local only).
- **Health Checks**: Integrated Docker health checks for service reliability.

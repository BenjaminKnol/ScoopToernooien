# SHC Scoop – Field Hockey Tournament Manager

A Laravel-based application to organize and manage field hockey tournaments for the SHC Scoop student association. Local development uses Laravel Sail (Docker). Deployment will likely be on Microsoft Azure; details are being worked out.

## Tech stack
- PHP 8.x, Laravel 10/11
- Laravel Sail (Docker: PHP-FPM, MySQL/MariaDB, Redis, Mailpit)
- Composer, Node.js (for front-end assets if applicable)

---

## Prerequisites
- Docker Desktop installed and running
- Composer available on host (to initialize the project)
- Optional: Node.js and npm (if building front-end assets)

## Getting started (Laravel Sail)
1. Copy environment file and set your values:
   - cp .env.example .env
   - Update DB_*, APP_URL, MAIL_*, etc.
2. Install dependencies:
   - composer install
3. Start the Sail containers:
   - ./vendor/bin/sail up -d
4. Generate app key:
   - ./vendor/bin/sail artisan key:generate
5. Run migrations and seeders (if available):
   - ./vendor/bin/sail artisan migrate --seed
6. Visit the app:
   - http://localhost (or the port you set in .env)

## Common Sail commands
- Start containers: `./vendor/bin/sail up -d`
- Stop containers: `./vendor/bin/sail down`
- Run Artisan: `./vendor/bin/sail artisan <command>`
- Run tests: `./vendor/bin/sail test`
- Run Composer: `./vendor/bin/sail composer <args>`
- Run NPM: `./vendor/bin/sail npm <args>`

## Database
- Configure DB connection in .env (defaults to the mysql container from Sail)
- Use migrations to create schema; seeders/factories can generate test data (e.g., teams, players, timeslots)

## Testing
- `./vendor/bin/sail test`
- Optionally configure phpunit.xml for coverage, etc.

## Deployment (Azure – planned)
Azure deployment is intended but not finalized. Pending decisions include:
- Runtime: Azure App Service for Linux (PHP) vs. Container Apps / Azure Kubernetes Service (AKS)
- Database: Azure Database for MySQL Flexible Server
- Storage: Azure Blob Storage for assets/logs (if needed)
- Cache/Queue: Azure Cache for Redis / Storage Queues
- CI/CD: GitHub Actions or Azure Pipelines (build, test, deploy)

Proposed next steps:
- Choose target service (App Service vs. containers)
- Add production .env template and Key Vault integration
- Add deployment workflow (GitHub Actions) with steps: build → test → asset build → containerize (if used) → deploy → run migrations

## Contributing
- Use feature branches and pull requests
- Follow Laravel/PHP-CS-Fixer coding standards (add a fixer config if not present)
- Add/maintain tests for new features

## License
Specify license (e.g., MIT) once confirmed by the project owner.

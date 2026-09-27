# MASAR Backend

Backend REST API for **MASAR**, a platform that connects students with companies
offering training and internship opportunities.

The backend is a custom, framework-free **PHP 8.2+** application using a
flat-file modular architecture, MySQL/MariaDB via PDO, and JWT authentication.

## Stack

| Layer | Technology |
| --- | --- |
| Language | PHP `^8.2` (runs on 8.2 / 8.3 / 8.4) |
| HTTP entry | `public/index.php` (Apache + `.htaccess` rewrite) |
| Database | MySQL / MariaDB (`masar`) through PDO prepared statements |
| Auth | JWT (HS256) access tokens + refresh cookie + Google OAuth |
| Mail | PHPMailer (SMTP, Gmail defaults) |
| Dependencies | Composer: `google/apiclient`, `phpmailer/phpmailer`, `vlucas/phpdotenv`, `phpunit/phpunit` |

## Requirements

- PHP 8.2+ with `pdo_mysql`, `mbstring`, `json`, `openssl`, `curl`
- MySQL 8 / MariaDB 10.4+
- Composer 2
- Apache with `mod_rewrite` (Laragon on Windows is the supported local setup)

## Quick start (local)

```bash
cd backend

# 1. Install dependencies
composer install

# 2. Create your environment file and fill in real values
cp .env.example .env

# 3. Create the database and import the schema
mysql -u root -e "CREATE DATABASE IF NOT EXISTS masar CHARACTER SET utf8mb4;"
mysql -u root masar < database/schema/masar.sql

# 4. Seed lookup/demo data (optional, standalone scripts)
php database/seeders/study_fields_seeder.php
php database/seeders/specializations_seeder.php

# 5. Point your web server at the backend directory and open
#    http://localhost/Masar/backend/api/v1/health
```

A full Windows/Laragon walkthrough (virtual host, PHP extensions, imports) lives
in [`docs/SETUP_LARAGON.md`](docs/SETUP_LARAGON.md). Production notes are in
[`DEPLOYMENT.md`](DEPLOYMENT.md).

## API

- Base URL: `http://localhost/Masar/backend/api/v1`
- Format: JSON; versioned under `/api/v1`
- Auth: `Authorization: Bearer <access token>` + HttpOnly refresh cookie
- Machine-readable contract: [`openapi.yaml`](openapi.yaml)
- Per-domain reference: [`docs/api/`](docs/api/)

## Project layout

```text
backend/
|-- public/            # Front controller and web root
|-- app/
|   |-- config/        # app, constants, cors, database, mail, upload
|   |-- core/          # auth, cron, database, errors, helpers, http,
|   |                  # logging, middleware, validation
|   |-- modules/       # admin, auth, certificates, companies, files,
|   |                  # messaging, notifications, payments, search,
|   |                  # students, training, users
|   |-- services/      # jwt_service.php
|   |-- shared/        # enums/ + functions/
|   `-- storage/       # runtime uploads/cache (not committed)
|-- routes/            # path-based route definitions per domain
|-- database/
|   |-- schema/        # masar.sql (source of truth)
|   |-- migrations/    # incremental SQL migrations
|   `-- seeders/       # standalone seeding scripts
|-- cron/              # scheduled background scripts
|-- docs/              # API, architecture, database, security, setup docs
|-- tests/             # standalone endpoint/regression runners
|-- config/            # reference YAML configs (cache, logging, monitoring, alerts)
|-- openapi.yaml
|-- composer.json
`-- .env.example
```

## Testing and quality

```bash
composer test                       # PHPUnit (phpunit.xml)
php -l path/to/file.php             # PHP syntax lint
```

- The `tests/` directory contains **standalone endpoint/regression runners**
  (executed with `php tests/<name>.php`), not PHPUnit cases. `composer test`
  currently reports "No tests executed".
- Static analysis config: [`phpstan.neon`](phpstan.neon) (requires
  `phpstan/phpstan`; not yet a Composer dependency).
- Code style config: [`.php-cs-fixer.dist.php`](.php-cs-fixer.dist.php)
  (requires `friendsofphp/php-cs-fixer`; not yet a Composer dependency).

## Documentation

| Document | Purpose |
| --- | --- |
| [`ARCHITECTURE.md`](ARCHITECTURE.md) | System overview and layer responsibilities |
| [`DATABASE.md`](DATABASE.md) | Data model, entities, conventions |
| [`AUTH.md`](AUTH.md) | Authentication and authorization |
| [`ERRORS.md`](ERRORS.md) | Response envelope and error codes |
| [`SECURITY.md`](SECURITY.md) | Security model |
| [`SECURITY_CHECKLIST.md`](SECURITY_CHECKLIST.md) | Pre-deployment security checklist |
| [`DEPLOYMENT.md`](DEPLOYMENT.md) | Production deployment |
| [`CHANGELOG.md`](CHANGELOG.md) | Notable changes |
| [`docs/AGENTS.md`](docs/AGENTS.md) | Authoritative engineering rules for AI agents |
| [`docs/SETUP_LARAGON.md`](docs/SETUP_LARAGON.md) | Local Windows/Laragon setup |

## Contributing

Follow the project rules in [`docs/AGENTS.md`](docs/AGENTS.md): thin controllers,
business logic in services, all SQL in repositories, parameterized queries only,
and never commit `.env`, secrets, or `vendor/`.

# MASAR Backend - Deployment

> Local Windows/Laragon development setup: [`docs/SETUP_LARAGON.md`](docs/SETUP_LARAGON.md).
> This document covers a production deployment.

## Requirements

- PHP 8.2+ (`pdo_mysql`, `mbstring`, `json`, `openssl`, `curl`, `fileinfo`)
- MySQL 8 / MariaDB 10.4+
- Apache 2.4 with `mod_rewrite`, or an equivalent server that honors
  `.htaccess`
- Composer 2
- HTTPS certificate for the API hostname

## 1. Configure the environment

```bash
cp .env.example .env
```

Set at minimum:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://api.example.com
APP_TIMEZONE=Africa/Cairo
FRONTEND_URL=https://app.example.com
CORS_ALLOWED_ORIGINS=https://app.example.com

DB_HOST=127.0.0.1
DB_DATABASE=masar
DB_USERNAME=masar_app
DB_PASSWORD=<strong-password>

JWT_SECRET=<long-random-secret>
SECURE_COOKIES=true
RATE_LIMIT_ENABLED=true

MAIL_*=<real SMTP credentials>
```

Never commit `.env`. Ensure it is outside version control and not served.

## 2. Install dependencies

```bash
composer install --no-dev --optimize-autoloader
```

`vendor/` must not be web-accessible.

## 3. Prepare the database

```bash
mysql -u root -e "CREATE DATABASE masar CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root masar < database/schema/masar.sql

# Apply incremental migrations (026-034) in order
for f in database/migrations/*.sql; do mysql -u root masar < "$f"; done

# The schema dump already includes the core lookup data (universities,
# faculties, degrees, study_fields, specializations, skills). Re-running the
# idempotent lookup seeders is optional:
php database/seeders/universities_seeder.php
php database/seeders/faculties_seeder.php
php database/seeders/degrees_seeder.php
php database/seeders/study_fields_seeder.php
php database/seeders/specializations_seeder.php
php database/seeders/skills_seeder.php
```

Do **not** run the test/demo seeders in production: `auth_registration_test_seeder`,
`backend_development_trainings_seeder`, `backend_spec199_test_data_seeder`,
`company_logo_seeder`, `demo_data_seeder`, `full_test_data_seeder`,
`training_matching_seeder`, `training_spec_inheritance_seeder`, `users_seeder`.

> `database/seeders/rejection_reasons_seeder.php` currently targets a
> `rejection_reasons` table that is absent from `database/schema/masar.sql` and
> cannot run as-is. Rejection reasons are served from
> `app/shared/enums/rejection_reasons.php`; do not schedule this seeder.

Create the application DB user with least privilege (data access only for the
`masar` schema - no `DROP`, `GRANT`, or global privileges).

## 4. Configure the web server

Recommended: point the document root at `public/` (keeps `app/`, `vendor/`,
`.env`, and `storage/` above the web root).

```apache
<VirtualHost *:443>
    ServerName api.example.com
    DocumentRoot "/var/www/masar/backend/public"

    <Directory "/var/www/masar/backend/public">
        AllowOverride All
        Require all granted
    </Directory>

    SSLEngine on
    SSLCertificateFile      /etc/ssl/certs/api.example.com.crt
    SSLCertificateKeyFile   /etc/ssl/private/api.example.com.key
</VirtualHost>
```

If you must use the `backend/` directory itself as the document root, keep the
shipped root `.htaccess` enabled (`AllowOverride All`); it forwards requests to
`public/index.php` and denies direct access to `.env`, `app/`, `database/`,
`routes/`, `storage/`, `tests/`, `docs/`, `postman/`, and `vendor/`.

Recommended PHP settings for uploads:

```ini
upload_max_filesize = 12M
post_max_size = 24M
memory_limit = 256M
```

## 5. Set filesystem permissions

The web user needs write access to runtime storage only:

```bash
chown -R www-data:www-data storage app/storage
find storage app/storage -type d -exec chmod 775 {} \;
```

These directories must not be web-accessible and must never contain executable
scripts.

## 6. Schedule cron jobs

Schedule the background scripts (all idempotent) with your system scheduler:

```cron
*/5 * * * * php /var/www/masar/backend/cron/close_expired_trainings.php
0 * * * *   php /var/www/masar/backend/cron/expire_trial_periods.php
0 * * * *   php /var/www/masar/backend/cron/send_expiry_notifications.php
30 3 * * *  php /var/www/masar/backend/cron/cleanup_temp_files.php
0 4 * * *   php /var/www/masar/backend/cron/cleanup_expired_tokens.php
0 2 * * *   php /var/www/masar/backend/cron/cleanup_audit_logs.php
```

> `cleanup_audit_logs.php` deletes **all** rows from `audit_logs` (full purge,
> no retention period). Review your audit-retention requirements before
> scheduling it; omit it if you need to keep audit history.

The HTTP cron hook (`/cron`, `/cron/run`) must be protected (secret token
and/or IP allowlist) or blocked at the web server.

## 7. Verify

- `GET https://api.example.com/api/v1/health` -> `success: true`
- Login -> token + refresh cookie; refresh works
- Protected endpoint -> 401 without token, 200 with token
- Cross-role request -> 403
- Upload a CV and a logo -> stored under `storage/`, served via download endpoints

Run the pre-deployment checks in [`SECURITY_CHECKLIST.md`](SECURITY_CHECKLIST.md).

## 8. Backups and rollback

- Back up the database and `storage/` (uploads) before every release.
- Keep the previous release directory plus a DB dump to roll back:
  deploy previous code, then restore the matching database snapshot.
- Rotate `JWT_SECRET` only with a planned re-login, since existing access tokens
  become invalid.

## 9. Maintenance

- Review `storage/logs/app.log` and rotate/trim logs.
- Run `composer audit` regularly and keep dependencies updated.
- Remove stale files from `storage/cache/security` if the limiter ever grows
  unbounded.

# MASAR Backend — Setup Guide for Windows + Laragon

This guide explains how to set up and run the **MASAR Backend** from scratch on a fresh
Windows machine using **Laragon**.

The guide is based entirely on the actual project files
(`composer.json`, `composer.lock`, `.env.example`, `.htaccess`, `public/index.php`,
`app/config/*`, and the repository structure).

> **Important:** The MASAR Backend is a plain **PHP REST API** (no PHP framework such as
> Laravel or Symfony). It uses **PDO** to talk to **MySQL**, **JWT** for authentication,
> **Google OAuth** for sign-in, and **PHPMailer** for sending email.

---

## 1. Installing Laragon

### 1.1 Where to download Laragon

Download Laragon from the official website:

- **https://laragon.org/download**

Choose the **Full** edition (`laragon-wamp.exe`, ~173 MB). It already includes
Apache 2.4, Nginx, PHP 8.x, MySQL 8, Composer, Node.js, git, and phpMyAdmin support.

### 1.2 System requirements

- Windows 10 / 11 (64-bit recommended).
- Administrator access to the machine.
- **Stop other web servers first** (XAMPP, WAMP, IIS, or a running `nginx`/`apache`) so
  they do not fight over ports 80/443/3306.

### 1.3 Installation steps

1. Run the downloaded installer (`laragon-wamp.exe`) **as Administrator**.
2. When Windows shows a User Account Control prompt, click **Yes**.
3. Keep the default installation location:

   ```
   C:\laragon
   ```

   > Do **not** install into a path containing spaces or `Program Files`
   > (e.g. `C:\Program Files\Laragon`). Long/space paths break some Apache/MySQL
   > tooling.
4. Finish the wizard. The installer creates:
   - `C:\laragon\www` — the default document root where project folders live.
   - `C:\laragon\bin` — isolated binaries (php, apache, mysql, etc.).
   - `C:\laragon\etc` — configuration files (apache2, php, mysql, etc.).
5. Launch Laragon.

### 1.4 How to start Laragon

- Double-click the **Laragon** shortcut, or run `C:\laragon\laragon.exe`.
- Laragon runs from the system tray. Click **Start All** to start Apache and MySQL
  together (or use the tray menu → **Start Apache** / **Start MySQL**).
- Optional: enable **Menu → Preferences → Auto start at boot** if you want Laragon to
  start automatically with Windows.

### 1.5 How to verify Apache and MySQL are running

In the Laragon main window:

- **Apache** and **MySQL** rows turn **green** when running.
- The status text changes from "STOPPED" to the running process name (e.g. `Apache 2.4.x`
  and `MySQL 8.x`).

Browser checks:

- Open `http://localhost` — you should see Laragon's default page (or a directory listing).
- Open `http://localhost/phpmyadmin` — the phpMyAdmin login page should load
  (login with `root` and an empty password, Laragon's default).

Command-line checks:

```bash
apache -v
mysql --version
```

### 1.6 Laragon settings required by this project

Laragon works out of the box for this project. No special Laragon setting is required
beyond the defaults:

- **Apache** as the web server (mod_rewrite is enabled by default).
- **Document root** = `C:\laragon\www` (default).
- **`AllowOverride All`** for the document root (default in Laragon), so the project's
  `.htaccess` rewrite rules are honoured.
- **MySQL** as the database server (default).
- **PHP 8.2 or newer** selected (see sections 2 and 4 for the required version).

A dedicated **Virtual Host is NOT required** (see section 6.2) — the project already
ships `.htaccess` rewrite rules that make it work from the `www` sub-folder path.

---

## 2. Using Quick Add in Laragon

**Quick Add** is used to install additional tools and extra versions into Laragon:

> **Menu → Tools → Quick Add**

### 2.1 What the MASAR Backend actually requires

The backend is a pure PHP + MySQL application. From the actual project files
(`composer.json`, `.env.example`, `app/config/database.php`) the required tools are:

| Tool | Required? | Why |
| --- | --- | --- |
| **PHP** | ✅ Required | The application is written in PHP (>= 8.2). |
| **Apache** | ✅ Required | Serves the app and runs the `.htaccess` rewrite rules. |
| **MySQL** | ✅ Required | The application connects to MySQL via PDO (database name `masar`). |
| **phpMyAdmin** | ✅ Recommended | Simplest way to import `database/schema/masar.sql`. |
| **Composer** | ✅ Required | Installs the PHP dependencies from `composer.json`. |
| **Node.js** | ❌ Not required | The **Backend** contains no JavaScript/Node code. Node is only used by the separate `frontend/` folder in the monorepo, not by the Backend. |

Laragon **Full** ships PHP, Apache, MySQL, and Composer already. **Quick Add is only
needed** if you want a specific extra version or phpMyAdmin.

### 2.2 What to add with Quick Add

1. **PHP (>= 8.2)**

   The project requires **PHP `^8.2`** (see section 4). Laragon Full ships PHP 8.4,
   which satisfies this. If you need another version:

   > **Menu → Tools → Quick Add → [PHP Version]** (e.g. `php-8.3` / `php-8.4`)

   Then activate it:

   > **Menu → PHP → Version → [the installed version]**

   Recommended: **PHP 8.3 or 8.4**.

2. **phpMyAdmin**

   > **Menu → Tools → Quick Add → phpmyadmin**
   >
   > If you use PHP 8.4+, prefer **phpMyAdmin 6**: `Menu → Tools → Quick Add → phpmyadmin6.0snapshot`

   (Laragon does not include phpMyAdmin by default; it is only a convenience for
   importing the SQL dump — you could also import with the `mysql` CLI.)

3. **MySQL / Apache** — only if you want a different version than the one Laragon ships.

   > **Menu → Tools → Quick Add → [Apache Version]** / **Menu → Tools → Quick Add → [MySQL Version]**

   Laragon's bundled MySQL 8 satisfies the project (the dump was generated on
   MySQL 9.1; see section 7).

### 2.3 After adding

After installing a new version, restart the corresponding service from the Laragon
window (right-click the service row → **Restart**), then verify with:

```bash
php -v
mysql --version
apache -v
```

---

## 3. Composer

### 3.1 Install Composer on Windows

Laragon **Full** bundles Composer, so no separate install is needed.

If Composer is not present, install it from the official installer:

- **https://getcomposer.org/download/**

Run the `Composer-Setup.exe` wizard and let it detect the PHP binary. If you install
Composer **outside** Laragon, keep in mind you must point it at Laragon's PHP
(see section 3.2).

### 3.2 Make sure Composer uses Laragon's PHP version

Laragon already exports its PHP/Composer paths into its integrated **Terminal**
(use the **Terminal** button in the Laragon window, or `Menu → Terminal`). Commands
run there automatically use Laragon's PHP.

Verify which PHP Composer is using:

```bash
composer --version
php -v
```

If Composer reports a *different* PHP than Laragon's (e.g. a system-wide PHP), tell
Composer to use Laragon's PHP by running:

```powershell
composer config --global platform.php 8.3.0
```

or temporarily invoke Composer with Laragon's PHP:

```powershell
C:\laragon\bin\php\<php-version-dir>\php.exe C:\laragon\bin\composer\composer.phar --version
```

> Example: `C:\laragon\bin\php\php-8.3.x-Win32-vs17-x64\php.exe`

### 3.3 Verify Composer is working

```bash
php -v
composer -V
composer diagnose
```

`composer -V` should print a **Composer 2.x** version (the project's `composer.lock`
uses Composer plugin API `2.9.0`, so Composer **2.x** is required).

### 3.4 `php` or `composer` is not recognized

If running `php` or `composer` prints something like
`'php' is not recognized as an internal or external command`, the Laragon bin paths
are not on your `PATH`.

**Fix (recommended):** use the **Laragon Terminal** (`Menu → Terminal`) instead of the
plain Windows `cmd`/PowerShell. Laragon's terminal automatically adds its own
`bin\php`, `bin\composer`, etc. to the session `PATH`.

**Fix (permanent):** add Laragon to your Windows `PATH`:

1. Press <kbd>Win</kbd>, type "environment variables", open
   *Edit the system environment variables*.
2. In *System properties → Environment Variables…*, select **Path** and click **Edit**.
3. Add the following entries:

   ```
   C:\laragon\bin\php\php-8.3.x-Win32-vs17-x64
   C:\laragon\bin\composer
   ```

   (adjust the PHP folder name to the version you installed).
4. Click **OK** everywhere, open a **new** terminal, and test again:

   ```bash
   php -v
   composer -V
   ```

---

## 4. Composer Packages Used by MASAR Backend

These are the **direct dependencies** listed in the project's `composer.json`
(the table shows the constraint from `composer.json` and the exact version locked in
`composer.lock`):

| Package | Version (locked) | Purpose |
| ------- | ------- | ------- |
| `google/apiclient` | `^2.19` → `v2.19.4` | Official Google API client. Used for **Google OAuth sign-in** (`app/modules/auth/services/auth_service.php` creates `Google\Client`, handles the `/api/v1/auth/google` and `/api/v1/auth/google/callback` endpoints). |
| `phpmailer/phpmailer` | `^7.1` → `v7.1.1` | Full-featured email library. Used in `app/shared/functions/email.php` to send SMTP emails (verification codes, password-reset OTPs, notifications). |
| `vlucas/phpdotenv` | `^5.6` → `v5.6.4` | Loads environment variables from the `.env` file into `getenv()` / `$_ENV` (used in `public/index.php`). |
| `phpunit/phpunit` | `^10.5` → `10.5.64` | **Development-only** dependency. PHPUnit test framework referenced by the `composer test` script. |

> **PHP requirement:** `composer.json` requires **PHP `^8.2`** (also recorded as the
> platform in `composer.lock`). The transitive packages require PHP `^8.1`, so the
> project's `^8.2` is the binding constraint. **Use PHP 8.2 or newer (8.3 / 8.4
> recommended).**

### Transitive (indirect) dependencies

The packages below are present in `composer.lock` but are **not** listed directly in
`composer.json`. They are pulled in automatically by the direct dependencies and must
**not** be added to `composer.json` manually.

Notable ones (directly usable by the app even though they are transitive):

- `firebase/php-jwt` (v7.1.0) — used directly by `app/modules/auth/services/auth_service.php`
  to verify Google ID-token signatures during Google sign-in. It is a dependency of
  `google/apiclient`, which is why it is transitive.
- `google/auth`, `google/apiclient-services` — internal parts of the Google client stack.
- `guzzlehttp/guzzle`, `guzzlehttp/promises`, `guzzlehttp/psr7`, `monolog/monolog`,
  `psr/*`, `symfony/polyfill-*`, `symfony/deprecation-contracts`,
  `ralouphie/getallheaders`, `graham-campbell/result-type`, `phpoption/phpoption` —
  HTTP client, PSR interfaces, polyfills, and support libraries used by the
  Google/Guzzle stack and phpdotenv.
- PHPUnit's own toolchain in `packages-dev`: `myclabs/deep-copy`, `nikic/php-parser`,
  `phar-io/*`, `phpunit/*`, `sebastian/*`, `theseer/tokenizer`.

All of these are installed automatically when you run `composer install`.

---

## 5. Installing Dependencies

The project **contains a `composer.lock`**, so the correct command is:

```bash
composer install
```

Do **not** use `composer update` as the standard setup method (see the difference below).

### `composer install` vs `composer update`

- **`composer install`** — reads the exact versions pinned in `composer.lock` and
  installs precisely those. It is deterministic: every developer gets the same packages.
  This is the **correct** command for setting up a project that already has a lock file.
- **`composer update`** — ignores the locked versions, re-resolves all constraints
  against Packagist, and **rewrites `composer.lock`**. Use it only when you *intentionally*
  want to upgrade dependencies or change `composer.json`.

Run `composer install` from the project directory:

```bash
cd C:\laragon\www\Masar\backend
composer install
```

This creates/refills the `vendor/` folder with all packages listed in the
`Composer Packages Used by MASAR Backend` section (section 4).

---

## 6. Setting Up the Project Inside Laragon

### 6.1 Where the project belongs

Laragon serves every folder under its document root. The document root is:

```text
C:\laragon\www\
```

The MASAR repository is a monorepo: the Backend lives in the `backend/` sub-folder.
Place the repository so that the backend folder becomes:

```text
C:\laragon\www\Masar\backend
```

> Expected layout:
>
> ```text
> C:\laragon\www\Masar\
> ├── README.md
> ├── backend\        ← this guide's subject
> │   ├── app\
> │   ├── cron\
> │   ├── database\
> │   ├── docs\
> │   ├── public\
> │   ├── routes\
> │   ├── storage\
> │   ├── vendor\
> │   ├── .env
> │   ├── .htaccess
> │   ├── composer.json
> │   ├── composer.lock
> │   └── index.php
> └── frontend\       ← separate Next.js frontend (not covered by this guide)
> ```

The Backend is then reachable at:

```text
http://localhost/Masar/backend/
```

### 6.2 Apache requirements — what the project actually uses

Determined from the actual project files:

| Requirement | Needed? | Evidence |
| --- | --- | --- |
| **Virtual Host** | ❌ **Not required** | The project's base URL is `http://localhost/Masar/backend/` (`docs/AGENTS.md`, `docs/api/students.md`). It is served directly from the `www` sub-folder; Laragon's default vhost setup handles this without extra configuration. A dedicated vhost is optional. |
| **`public/` directory** | ✅ Yes (as front controller) | `public/index.php` is the real entry point (`public/index.php` requires `vendor/autoload.php`, loads `.env`, and dispatches routes). |
| **Apache rewrite rules** | ✅ Yes | `mod_rewrite` is required. Both `.htaccess` files use `RewriteEngine On`. |
| **`.htaccess`** | ✅ Yes | Two files are shipped: the root `backend/.htaccess` (rewrites every request to `public/index.php`, blocks direct access to `.env`, `vendor/`, `app/`, `routes/`, `storage/`, etc.) and `public/.htaccess` (front-controller rewrite inside `public/`). |
| **Document root** | `C:\laragon\www` (default) | The project works from its `www` sub-folder; no per-project document root is needed. |
| **Other Apache config** | None beyond defaults | `DirectoryIndex public/index.php index.php` is set by the root `.htaccess`. Laragon enables `mod_rewrite` and `AllowOverride All` by default. |

**Conclusion:** no manual Apache edit is required. Just make sure **Apache** is running
and **mod_rewrite is enabled** (default in Laragon). If you ever create a virtual host,
point it at `C:\laragon\www\Masar\backend` (or its `public/` folder) and keep the
`.htaccess` files intact.

### 6.3 The root `index.php`

`backend/index.php` simply forwards to the real entry point:

```php
require __DIR__ . '/public/index.php';
```

This lets Apache fall back to the folder-level entry when requested, but all normal
requests are routed to `public/index.php` by the `.htaccess` rewrite rules.

---

## 7. Database Setup

### 7.1 Required database server

- **MySQL** (Laragon bundles MySQL 8; the project dump was generated from MySQL 9.1).
- Start **MySQL** in Laragon before importing.

### 7.2 Expected database

The project defines the database name in `.env.example` and `app/config/database.php`:

```text
DB_DATABASE=masar
```

The complete schema — including table definitions **and data** — is provided in:

```text
database/schema/masar.sql
```

This dump already contains `CREATE DATABASE masar; USE masar;` plus all tables and
sample data. **Importing it creates the database for you.**

### 7.3 Create/import the database (phpMyAdmin)

1. Open `http://localhost/phpmyadmin`.
2. Log in with username `root` and an **empty password** (Laragon's default).
3. Click the **Import** tab.
4. Choose `C:\laragon\www\Masar\backend\database\schema\masar.sql`.
5. Click **Go**. The `masar` database is created with all tables and data.

### 7.4 Create the database (MySQL CLI — alternative)

```bash
mysql -u root -e "CREATE DATABASE IF NOT EXISTS masar CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root masar < C:\laragon\www\Masar\backend\database\schema\masar.sql
```

### 7.5 Expected `.env` database configuration

From `.env.example` (values are the Laragon defaults — safe for local development):

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=masar
DB_USERNAME=root
DB_PASSWORD=
```

| Setting | Expected value |
| --- | --- |
| Driver | `mysql` |
| Host | `127.0.0.1` |
| Port | `3306` |
| Database | `masar` |
| Username | `root` |
| Password | *(empty)* — Laragon's default root password is empty |

> **Security note:** the empty root password is fine for local development. Do not use
> it on a machine that is exposed to the internet.

### 7.6 Optional reference data

The folder `database/seeders/` contains PHP seeder scripts for reference data
(`universities`, `faculties`, `degrees`, `specializations`, `skills`,
`rejection_reasons`, `users`). The `masar.sql` dump already includes this reference data,
so running the seeders is **not** required for a basic setup. There is no Composer
script for seeding — if you need them, run them manually as CLI PHP scripts.

---

## 8. Environment Configuration

### 8.1 Create `.env` from `.env.example`

The Backend loads configuration from a `.env` file (via `vlucas/phpdotenv` in
`public/index.php`). `.env` is **not** committed to the repository (it is in
`.gitignore`), so you must create it:

```bash
cd C:\laragon\www\Masar\backend
copy .env.example .env
```

> `.env.example` ships with *sample* integration values (a Google client ID, a
> reCAPTCHA site key, and a Gmail address for mail). Replace every value with your own
> before going anywhere near production. Never commit the real `.env`.

### 8.2 Important variables

| Variable | Example | Purpose |
| --- | --- | --- |
| `APP_ENV` | `local` | Application environment (`local` / `development` / `production`). |
| `APP_DEBUG` | `true` | When `true`, verbose error details are returned. Keep `false` in production. |
| `APP_URL` | `http://localhost` | Application base URL. Used to build the Google OAuth redirect URI when `GOOGLE_REDIRECT_URI` is empty, and for cookie paths. |
| `APP_TIMEZONE` | `Africa/Cairo` | Application timezone (default fallback in `app/config/app.php`). Not present in `.env.example`, optional. |
| `DB_*` | see section 7.5 | MySQL connection settings (host, port, database, user, password). |
| `MAIL_DRIVER` | `smtp` | Mail transport used by PHPMailer. |
| `MAIL_HOST` | `smtp.gmail.com` | SMTP server host. |
| `MAIL_PORT` | `587` | SMTP port. |
| `MAIL_USERNAME` | `your-smtp-username` | SMTP login username. |
| `MAIL_PASSWORD` | `your-gmail-app-password` | SMTP password (**use an app password**, never your real Google password). |
| `MAIL_ENCRYPTION` | `tls` | SMTP encryption (`tls` / `ssl` / empty). |
| `MAIL_FROM_ADDRESS` | `no-reply@example.com` | "From" email address for outgoing messages. |
| `MAIL_FROM_NAME` | `MASAR` | Display name for the "From" address. |
| `UPLOAD_DIR` | `storage/uploads` | Where uploaded files are stored (relative to project root). |
| `CACHE_DIR` | `storage/cache` | Cache storage directory. |
| `LOG_DIR` | `storage/logs` | Log storage directory. |
| `GOOGLE_CLIENT_ID` | `your-google-client-id` | Google OAuth 2.0 client ID (Google Cloud Console → Credentials). |
| `GOOGLE_CLIENT_SECRET` | `your-google-client-secret` | Google OAuth 2.0 client secret (keep it secret). |
| `GOOGLE_REDIRECT_URI` | *(empty)* | When empty, the app derives it from `APP_URL` as `APP_URL + /api/v1/auth/google/callback`. Must match the URI registered in the Google Cloud console. |
| `RECAPTCHA_SITE_KEY` | `your-recaptcha-site-key` | Google reCAPTCHA site key (used by the frontend). |
| `RECAPTCHA_SECRET_KEY` | `your-recaptcha-secret-key` | reCAPTCHA server-side secret (keep it secret). |
| `RECAPTCHA_MIN_SCORE` | `0.5` | Minimum reCAPTCHA v3 score to accept a request. |
| `JWT_SECRET` | `your-secret-here` | HMAC-SHA256 signing secret for JWTs (used by `app/services/jwt_service.php`). **Use a long random string.** |
| `JWT_ALGORITHM` | `HS256` | JWT signing algorithm (`HS256` / `HS384` / `HS512`). |
| `JWT_ACCESS_TTL` | `3600` | Access-token lifetime in seconds. |
| `JWT_REFRESH_TTL` | `2592000` | Refresh-token lifetime in seconds (default 30 days). |
| `CSRF_ENABLED` | `true` | Enables CSRF protection for cookie-authenticated state-changing requests. |
| `CSRF_COOKIE_NAME` | `csrf_token` | Name of the CSRF cookie. |
| `CSRF_HEADER_NAME` | `X-CSRF-Token` | Header name expected on state-changing requests. |
| `SECURE_COOKIES` | `true` | Whether auth/refresh cookies are `Secure` (keep `false` for plain `http://localhost`; use `true` under HTTPS). |
| `SECURITY_HEADERS_ENABLED` | `true` | Whether the app adds security HTTP headers. |
| `CSP_ENABLED` | `true` | Whether a Content-Security-Policy header is applied. |

Additional (optional, read by `app/config/app.php`, not in `.env.example`):

| Variable | Default | Purpose |
| --- | --- | --- |
| `CORS_ALLOWED_ORIGINS` | `http://localhost:3000,http://localhost:5173` | Comma-separated origins allowed by CORS (frontend URLs). |

---

## 9. Running the Project

Complete setup order on a fresh Windows machine with Laragon:

### Step 1 — Install and start Laragon

Install Laragon (section 1), then in the Laragon window click **Start All** so that
Apache and MySQL are green.

### Step 2 — Ensure PHP 8.2+

In Laragon: **Menu → PHP → Version** — pick PHP 8.3 or 8.4 (requires `^8.2`).

Verify:

```bash
php -v
```

### Step 3 — Verify Composer

```bash
composer -V
```

Should report Composer **2.x**.

### Step 4 — Place the project

Make sure the Backend is at:

```text
C:\laragon\www\Masar\backend
```

### Step 5 — Install PHP dependencies

```bash
cd C:\laragon\www\Masar\backend
composer install
```

This installs the exact packages from `composer.lock` into `vendor/`.

### Step 6 — Create the database

Import `database/schema/masar.sql` via phpMyAdmin (section 7.3), or run:

```bash
mysql -u root -e "CREATE DATABASE IF NOT EXISTS masar CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root masar < C:\laragon\www\Masar\backend\database\schema\masar.sql
```

### Step 7 — Configure the environment

```bash
cd C:\laragon\www\Masar\backend
copy .env.example .env
```

Edit `.env` and at minimum set a strong `JWT_SECRET`, plus your own Google OAuth and
mail credentials (section 8). Keep the Laragon defaults for the `DB_*` settings.

### Step 8 — Start the Backend

The Backend is served by Apache — there is nothing to "run" manually:

1. Confirm Apache and MySQL are running in Laragon.
2. Open in your browser:

   ```text
   http://localhost/Masar/backend/
   ```

   You should see the **MASAR Backend** API documentation landing page.

### Step 9 — Verify the API

- Health check (returns JSON):

  ```text
  http://localhost/Masar/backend/health
  ```

  Expected response:

  ```json
  { "success": true, "message": "Service is healthy.", "data": { "status": "ok" } }
  ```

- API root:

  ```text
  http://localhost/Masar/backend/api/v1
  ```

- API health:

  ```text
  http://localhost/Masar/backend/api/v1/health
  ```

- Full API base URL (used by the frontend and the Postman collection):

  ```text
  http://localhost/Masar/backend/api/v1
  ```

> If the landing page renders but `/health` returns a 404, the Apache rewrite rules are
> not active — see section 11 ("404 Not Found" / rewrite problems).

---

## 10. Verification Checklist

- [ ] Laragon installed in `C:\laragon`
- [ ] Apache running (green in the Laragon window)
- [ ] MySQL running (green in the Laragon window)
- [ ] Correct PHP version selected — `php -v` reports **8.2 or newer**
- [ ] Composer installed — `composer -V` reports **Composer 2.x**
- [ ] `composer install` completed successfully in `C:\laragon\www\Masar\backend`
- [ ] `vendor/` exists and contains the locked packages (e.g. `vendor/autoload.php` exists)
- [ ] `.env` created from `.env.example` and edited (database, `JWT_SECRET`, mail, OAuth)
- [ ] Database `masar` created and `database/schema/masar.sql` imported
- [ ] Database connection verified (no "could not connect" errors in the API)
- [ ] Backend accessible — `http://localhost/Masar/backend/` shows the API landing page
- [ ] Health endpoint responds — `http://localhost/Masar/backend/health` returns JSON `{"status":"ok"}`
- [ ] API root responds — `http://localhost/Masar/backend/api/v1` returns MASAR API info
- [ ] `/api/v1/health` responds — `http://localhost/Masar/backend/api/v1/health`
- [ ] A request to `/api/v1/auth/register` (or any protected route) returns a JSON
      response (and not an HTML 404 / directory listing)
- [ ] No sensitive files are reachable over HTTP: `http://localhost/Masar/backend/.env`
      must return **403 Forbidden** (blocked by `.htaccess`)
- [ ] Required PHP extensions available (see `php -m`): `pdo_mysql`, `openssl`,
      `mbstring`, `curl`, `fileinfo`, `ctype`, `json`

---

## 11. Common Problems

### 11.1 `404 Not Found`

**Likely cause:** Apache rewrite rules are not being applied, so requests such as
`/health` or `/api/v1/...` are never routed to `public/index.php`.

**How to verify:**
- Open `http://localhost/Masar/backend/` — if the landing page loads but
  `/health` returns 404, rewriting is broken.
- Create `public/index.php` check: run
  `http://localhost/Masar/backend/index.php/health` — if this works while
  `/health` does not, the rewrite rules are bypassed.

**Solution:**
- Make sure `mod_rewrite` is loaded. In Laragon: **Menu → Apache → Modules → mod_rewrite**
  should be enabled (it is by default).
- Make sure `AllowOverride All` is set for `C:\laragon\www`. Laragon's default
  `httpd.conf` uses `AllowOverride All` for the document root.
- Confirm both `.htaccess` files exist and were not modified:
  `backend/.htaccess` and `backend/public/.htaccess`.
- Do **not** delete the root `backend/index.php` — the `.htaccess` `DirectoryIndex`
  references `public/index.php index.php`.
- Restart Apache after any config change: Laragon window → right-click Apache → **Restart**.

### 11.2 `500 Internal Server Error`

**Likely cause:** a PHP error during bootstrap — most often a missing `vendor/`,
a missing `.env`, a wrong database configuration, or a PHP version mismatch.

**How to verify:**
- Check the Laragon Apache error log:
  `C:\laragon\etc\apache2\logs\error.log` (or via **Menu → Tools → Logs Viewer**).
- Check the application log: `C:\laragon\www\Masar\backend\storage\logs\`.
- With `APP_DEBUG=true`, the API returns the error message in the JSON response.

**Solution:**
- Ensure `composer install` was run and `vendor/autoload.php` exists (see 11.8).
- Ensure `.env` exists and is valid (see 11.10).
- Check the database settings in `.env` (see 11.9).
- Ensure PHP 8.2+ is selected in Laragon (see 11.4).

### 11.3 PHP version mismatch

**Likely cause:** Laragon is running an older PHP (e.g. 7.x) while the project needs
`^8.2`; or Composer is bound to a different PHP than the web server.

**How to verify:**
```bash
php -v
```
must report **8.2 or newer**. Also compare with the version shown in the Laragon window
(**Menu → PHP → Version**) and the one the web server uses (check the app's `/health`
response or a temporary `phpinfo()`).

**Solution:**
- Install PHP 8.2+ via **Menu → Tools → Quick Add → [PHP version]** if missing.
- Switch: **Menu → PHP → Version → 8.3/8.4**, then restart Apache.
- Re-run `composer install` after switching so dependencies resolve against the
  correct PHP platform.

### 11.4 Composer dependency errors

**Likely cause:** the wrong PHP version/extensions are active, or `composer install`
was interrupted/corrupted.

**How to verify:**
- Run `composer install` again and read the error. Common ones:
  - `Your lock file does not contain a compatible set of packages` — the active PHP
    version does not satisfy the constraints in `composer.lock`.
  - `The "X" PHP extension is required` — an extension is disabled in `php.ini`.

**Solution:**
- Switch to PHP 8.2+ in Laragon and retry (see 11.3).
- Enable required extensions in Laragon's PHP settings:
  **Menu → PHP → Quick Settings** (or edit `C:\laragon\bin\php\<version>\php.ini`)
  — `pdo_mysql`, `openssl`, `mbstring`, `curl`, `fileinfo`, `ctype`, `json`.
- Delete `vendor/` and run `composer install` again:

  ```bash
  cd C:\laragon\www\Masar\backend
  Remove-Item -Recurse -Force vendor
  composer install
  ```

  Do **not** run `composer update` to "fix" lock errors (see section 12).

### 11.5 `composer` command not recognized

See section 3.4 — use the **Laragon Terminal**, or add `C:\laragon\bin\composer` to
your Windows `PATH`.

### 11.6 `php` command not recognized

See section 3.4 — use the **Laragon Terminal**, or add the active PHP folder
(`C:\laragon\bin\php\php-8.3.x-Win32-vs17-x64`) to your Windows `PATH`.

### 11.7 Apache not starting

**Likely cause:** another service (XAMPP, WAMP, IIS, Skype, Docker) is occupying
ports **80/443**, or Apache failed to bind.

**How to verify:**
- Check the Apache error log: `C:\laragon\etc\apache2\logs\error.log`.
- Check which process holds the port:
  ```powershell
  netstat -ano | findstr :80
  ```

**Solution:**
- Stop the conflicting program (or run Laragon **as Administrator**).
- In Laragon: **Menu → Apache → httpd.conf**, confirm the `Listen` ports.
- Restart Apache: Laragon window → right-click Apache → **Restart**.

### 11.8 MySQL not starting

**Likely cause:** port **3306** is occupied by another MySQL instance, or the MySQL
data folder is corrupted / not writable.

**How to verify:**
- Check `C:\laragon\etc\mysql\...` logs (Laragon → right-click MySQL → **Logs**).
- ```powershell
  netstat -ano | findstr :3306
  ```

**Solution:**
- Stop other MySQL/MariaDB/XAMPP services, then restart MySQL in Laragon.
- If the data folder is broken, use Laragon's menu **Menu → Database → Repair MySQL**
  (this is a destructive operation — back up data first).

### 11.9 `.htaccess` / rewrite problems

**Likely cause:** `mod_rewrite` disabled or `AllowOverride None` for the document root.

**How to verify / solution:** identical to 11.1. Confirm `mod_rewrite` is enabled and
`AllowOverride All` is in effect, and that the project's `.htaccess` files were not
edited or deleted.

### 11.10 Wrong Document Root

**Likely cause:** the project is not under `C:\laragon\www`, or a virtual host points
elsewhere.

**How to verify:**
- Laragon: **Menu → www → Switch Document Root** — should show `C:\laragon\www`.
- Confirm the project folder path:
  ```
  C:\laragon\www\Masar\backend
  ```

**Solution:**
- Keep the Backend under the document root exactly as in section 6.1.
- If a virtual host is used, set its `ROOT` to `C:/laragon/www/Masar/backend` (or its
  `public/` folder) and keep `.htaccess` intact.

### 11.11 Wrong PHP version

**Likely cause:** Laragon's selected PHP version does not satisfy `^8.2`.

**How to verify / solution:** see 11.3. `php -v` must report 8.2+ and Composer must
use the same PHP.

### 11.12 Missing `vendor/autoload.php`

**Likely cause:** dependencies were never installed, or `vendor/` was deleted.

**How to verify:**
```powershell
Test-Path C:\laragon\www\Masar\backend\vendor\autoload.php
```

**Solution:**
```bash
cd C:\laragon\www\Masar\backend
composer install
```
(Do not create `vendor/` manually — see section 12.)

### 11.13 Database connection errors

**Likely cause:** MySQL not running, wrong credentials/host/port in `.env`, or the
`masar` database does not exist.

**How to verify:**
- Confirm MySQL is green in Laragon.
- Check `.env` matches section 7.5.
- Test the connection from the CLI:
  ```bash
  mysql -u root -e "SELECT 1;"
  ```
- Confirm the database exists:
  ```bash
  mysql -u root -e "SHOW DATABASES;"
  ```

**Solution:**
- Start MySQL.
- Correct `DB_HOST` / `DB_PORT` / `DB_DATABASE` / `DB_USERNAME` / `DB_PASSWORD`.
- Import `database/schema/masar.sql` to create the `masar` database (section 7.3).

### 11.14 Missing `.env`

**Likely cause:** `.env` was never created from `.env.example`.

**How to verify:**
```powershell
Test-Path C:\laragon\www\Masar\backend\.env
```

**Solution:**
```bash
cd C:\laragon\www\Masar\backend
copy .env.example .env
```
Then edit the values (section 8). The app loads `.env` via phpdotenv; without it the
configuration falls back to hard-coded defaults (which still work locally, but you will
not get your custom secrets).

---

## 12. Important Rules

- **Do not run `composer update` without a specific reason.** `composer update`
  rewrites `composer.lock` and can pull incompatible versions. Use `composer install`.
- **Do not delete `composer.lock`.** It is the source of truth for the exact dependency
  versions; without it, `composer install` cannot reproduce the environment.
- **Do not commit `.env` to Git.** It contains secrets (DB credentials, JWT secret,
  Google OAuth secret, SMTP password). It is already ignored by `.gitignore`.
- **Do not commit secrets to Git.** Never push real passwords, API keys, OAuth
  credentials, reCAPTCHA secrets, or the JWT secret. Use `.env` locally and keep
  `.env.example` filled with placeholders only.
- **Use `composer install` when setting up the project on a new machine.** It installs
  the exact locked versions and guarantees the same dependency set as the rest of the team.
- **Do not modify Apache/PHP configuration unless necessary.** Laragon's defaults
  satisfy this project. If you do edit `httpd.conf` or `php.ini`, keep a note of the
  change so it can be reproduced.
- **Use the PHP version compatible with the project.** The project requires PHP
  **^8.2** — use 8.2, 8.3 or 8.4 (8.3/8.4 recommended).
- **Keep the Composer dependencies synchronized with `composer.lock`.** Always run
  `composer install`, never `composer update`, unless a dependency change is intentional.
- **Do not manually create or modify the `vendor/` directory.** `vendor/` is generated
  by Composer. Delete and re-run `composer install` instead of hand-editing it.

---

## Known Issues

The following issues were found during the inspection. They are documented here and were
**not** fixed.

1. **`composer test` is broken out of the box.**
   `composer.json` defines the script `"test": "phpunit --configuration phpunit.xml"`,
   but **no `phpunit.xml` file exists** in the repository, and there is **no `tests/`
   directory**. Running `composer test` fails with "Could not read file phpunit.xml".
   If tests are needed, a `phpunit.xml` configuration file (and tests) must be added
   first.

2. **Duplicate keys in `.env.example`.**
   `UPLOAD_DIR`, `CACHE_DIR`, and `LOG_DIR` each appear twice in `.env.example`
   (lines 21–23 and 25–27). This is harmless (the last value wins when phpdotenv
   parses the file) but should be cleaned up.

3. **Repository-level documentation mismatch about the API URL.**
   The root `README.md` says the frontend expects the backend at
   `http://localhost:4000/api`, but the actual Backend runs at
   `http://localhost/Masar/backend/api/v1` under Laragon (as documented in
   `backend/docs/AGENTS.md` and `backend/docs/api/students.md`). Use the
   `http://localhost/Masar/backend/...` URLs for this Laragon setup.

4. **A real `.env` already exists in the working directory.**
   A `.env` file is present locally. It is correctly ignored by Git
   (`.gitignore` ignores `.env`), but it must never be committed or shared, and it may
   contain credentials that differ from `.env.example`.

5. **The schema dump was generated from MySQL 9.1.0 (PHP 8.4.5).**
   `database/schema/masar.sql` was exported with phpMyAdmin from MySQL Server 9.1.0.
   Importing into an older MySQL 8.x or MariaDB instance should normally work, but
   verify the import completes without errors (some newer SQL constructs may differ on
   older database servers).

6. **No dedicated virtual host is configured.**
   The project relies on `.htaccess` rewriting from the `www` sub-folder path
   (`http://localhost/Masar/backend/`). If you prefer a clean domain
   (e.g. `masar.test`), you must create a virtual host yourself; none is provided in
   the repository.
# MASAR

MASAR is a training and internship management platform that connects students with companies and provides a complete workflow for training opportunities, applications, sessions, certificates, messaging, notifications, files, and administrative operations.

---

## 1. Project Overview

MASAR provides the following major capabilities:

* User authentication.
* Student profiles.
* Company profiles.
* Training listings.
* Training applications.
* Training sessions.
* Certificates.
* Certificate appeals.
* Messaging.
* Notifications.
* File management.
* Payments.
* Search.
* Administrative management.
* Audit logging.

The system is designed as a modular monolithic PHP application.

---

## 2. Technology Stack

### Backend

* PHP 8.2+
* MySQL / MariaDB
* Composer

### Testing

* PHPUnit

### Configuration

* Environment variables using `vlucas/phpdotenv`

---

## 3. Project Structure

```text
MASAR/
│
├── app/
│   ├── controllers/
│   ├── services/
│   ├── repositories/
│   ├── models/
│   ├── middleware/
│   ├── validators/
│   └── ...
│
├── config/
│
├── database/
│   ├── migrations/
│   ├── seeders/
│   └── schema/
│
├── routes/
│
├── shared/
│   ├── enums/
│   └── functions/
│
├── public/
│
├── storage/
│   ├── logs/
│   ├── cache/
│   ├── uploads/
│   └── certificates/
│
├── cron/
│
├── tests/
│   ├── unit/
│   └── integration/
│
├── docs/
│   ├── api/
│   ├── database/
│   ├── architecture/
│   └── business_rules.md
│
├── .env
├── .env.example
├── .gitignore
├── composer.json
├── composer.lock
└── README.md
```

---

## 4. Requirements

Before running MASAR, install:

```text
PHP 8.2+
Composer
MySQL 8+ or MariaDB
Apache or another compatible web server
```

PHP extensions required by the application should be enabled according to the deployment environment.

---

## 5. Installation

Clone the project:

```bash
git clone <repository-url>
cd masar
```

Install Composer dependencies:

```bash
composer install
```

Create the environment file:

```bash
cp .env.example .env
```

On Windows:

```powershell
copy .env.example .env
```

Update `.env` with the local database and application configuration.

---

## 6. Database Setup

Create the database:

```sql
CREATE DATABASE masar
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;
```

Run the database migrations in order:

```text
001_create_users_table.sql
002_create_students_table.sql
003_create_companies_table.sql
...
023_create_audit_logs_table.sql
```

The complete schema is also available at:

```text
database/schema/masar.sql
```

---

## 7. Seed Data

Seeders are located in:

```text
database/seeders/
```

They provide initial/reference data such as:

* Users.
* Universities.
* Faculties.
* Degrees.
* Specializations.
* Skills.
* Rejection reasons.

Seeders should be executed only after the database schema has been created.

---

## 8. Application Entry Point

The public application entry point is:

```text
public/index.php
```

The web server document root should point to:

```text
/public
```

Do not expose the project root directly to the public web.

---

## 9. Development Server

For local development, the PHP built-in server can be used:

```bash
php -S localhost:8000 -t public
```

Then access:

```text
http://localhost:8000
```

---

## 10. Environment Configuration

Important environment variables include:

```text
APP_NAME
APP_ENV
APP_DEBUG
APP_URL

DB_HOST
DB_PORT
DB_DATABASE
DB_USERNAME
DB_PASSWORD

AUTH_SECRET

MAIL_HOST
MAIL_PORT
MAIL_USERNAME
MAIL_PASSWORD

STORAGE_PATH
UPLOADS_PATH
CERTIFICATES_PATH
```

Never commit the real `.env` file.

---

## 11. Directory Permissions

The application needs write access to runtime directories:

```text
storage/logs/
storage/cache/
storage/uploads/
storage/uploads/temp/
storage/certificates/
```

The exact permissions depend on the operating system and web-server user.

Do not make the entire project directory writable by the web server.

---

## 12. Routing

Routes are organized by domain:

```text
routes/
├── api.php
├── auth.php
├── users.php
├── students.php
├── companies.php
├── trainings.php
├── certificates.php
├── messaging.php
├── notifications.php
├── files.php
├── search.php
└── admin.php
```

The API entry point is:

```text
/api
```

---

## 13. Authentication

Protected API endpoints require authentication according to the configured authentication mechanism.

Authentication verifies the identity of the user.

Authorization verifies whether the authenticated user is allowed to perform the requested operation.

---

## 14. Main User Roles

MASAR supports role-based access control.

Typical roles include:

```text
Student
Company
Admin
```

The authoritative role values are defined in:

```text
shared/enums/user_roles.php
```

---

## 15. Training Workflow

The general training workflow is:

```text
Create Training
      ↓
Draft
      ↓
Publish
      ↓
Accept Applications
      ↓
Close Applications
      ↓
Training Sessions
      ↓
Complete Training
      ↓
Issue Certificates
```

Actual status transitions are controlled by the business rules.

---

## 16. Application Workflow

The general application workflow is:

```text
Student
   ↓
Apply
   ↓
Pending
   │
   ├── Accepted
   │      ↓
   │   Training Session
   │
   └── Rejected
```

Duplicate applications must be prevented.

---

## 17. Certificate Workflow

```text
Training Session
      ↓
Completion
      ↓
Eligibility Check
      ↓
Certificate Generation
      ↓
Certificate Storage
      ↓
Certificate Record
      ↓
Student Notification
```

---

## 18. Background Jobs

Scheduled jobs are located in:

```text
cron/
```

Available jobs:

```text
close_expired_trainings.php
expire_trial_periods.php
send_expiry_notifications.php
cleanup_temp_files.php
```

These should be registered with the server's scheduler/cron system.

---

## 19. Testing

Run all tests:

```bash
composer test
```

Run unit tests:

```bash
composer test-unit
```

Run integration tests:

```bash
composer test-integration
```

Tests are organized under:

```text
tests/
├── unit/
└── integration/
```

---

## 20. Code Standards

The project should follow:

* PSR-12 coding style.
* PSR-4 autoloading.
* Clear separation of responsibilities.
* Secure database access.
* Parameterized queries.
* Server-side validation.
* Centralized authorization.
* Consistent error handling.

---

## 21. Architecture

MASAR follows a modular monolithic architecture.

The main flow is:

```text
Request
   ↓
Router
   ↓
Middleware
   ↓
Controller
   ↓
Service
   ↓
Repository
   ↓
Database
```

Business logic belongs primarily in services/domain components.

Controllers should remain thin.

Repositories should handle persistence concerns.

---

## 22. Security

Security responsibilities include:

* Password hashing.
* Authentication.
* Authorization.
* Input validation.
* SQL injection prevention.
* File upload validation.
* Rate limiting.
* Secure storage.
* Audit logging.
* Protection of sensitive configuration.

Never trust client-side validation.

All security-sensitive checks must be performed server-side.

---

## 23. File Storage

Uploaded files are stored outside the public application directory whenever possible.

Storage locations include:

```text
storage/uploads/cvs/
storage/uploads/temp/
storage/certificates/
```

Files must be accessed through authorized application endpoints when they are private.

---

## 24. Logging

Application logs are stored in:

```text
storage/logs/
```

Logs must not contain:

```text
Passwords
Authentication tokens
API secrets
Payment credentials
Other sensitive credentials
```

---

## 25. Documentation

Documentation is located under:

```text
docs/
```

### API

```text
docs/api/
```

### Database

```text
docs/database/
```

### Architecture

```text
docs/architecture/
```

### Business Rules

```text
docs/business_rules.md
```

---

## 26. Database Documentation

Database architecture is documented in:

```text
docs/database/erd.md
docs/database/database_design.md
```

The complete schema is available in:

```text
database/schema/masar.sql
```

---

## 27. Development Workflow

Recommended workflow:

```text
1. Update database migration if needed.
2. Update model/repository.
3. Implement service/business rules.
4. Implement controller.
5. Register route.
6. Add/update validation.
7. Add tests.
8. Update API documentation.
9. Update business rules if required.
10. Run the complete test suite.
```

---

## 28. Pull Request Checklist

Before merging a feature:

```text
[ ] Business rules implemented
[ ] Authorization verified
[ ] Validation implemented
[ ] Database changes documented
[ ] Migration added if required
[ ] Tests added
[ ] API documentation updated
[ ] Error handling verified
[ ] Security reviewed
[ ] Logs contain no secrets
[ ] Composer autoload verified
```

---

## 29. Production Checklist

Before production deployment:

```text
[ ] APP_ENV=production
[ ] APP_DEBUG=false
[ ] Strong authentication secret configured
[ ] Production database configured
[ ] Database migrations executed
[ ] Required seed data loaded
[ ] Storage permissions configured
[ ] Web root points to /public
[ ] .env excluded from Git
[ ] Error display disabled
[ ] HTTPS configured
[ ] Cron jobs configured
[ ] Backups configured
[ ] Logging configured
[ ] Tests passing
```

---

## 30. Important Security Rule

Never deploy the project with:

```text
APP_DEBUG=true
```

in production.

Never expose:

```text
.env
database credentials
authentication secrets
private uploaded files
```

through the public web server.

---

## 31. Composer

Install dependencies:

```bash
composer install
```

Update dependencies only when intentionally changing dependency versions:

```bash
composer update
```

Regenerate autoload files:

```bash
composer dump-autoload
```

`composer.lock` should be committed to the repository for reproducible deployments.

---

## 32. License

This project is proprietary unless a separate license explicitly states otherwise.

---

## 33. Project Status

MASAR is structured as a modular PHP backend intended to support:

* Student training management.
* Company training management.
* Application workflows.
* Training sessions.
* Certificates.
* Communication.
* Notifications.
* Payments.
* Administration.

Implementation should follow the architecture and business rules documented in `docs/`.

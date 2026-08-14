# MASAR System Architecture

## 1. Overview

MASAR is a modular web platform that connects students with companies offering training opportunities.

The architecture is designed around clear separation of responsibilities between:

* HTTP/API handling.
* Authentication and authorization.
* Business logic.
* Data access.
* Database.
* Shared utilities.
* Background jobs.
* File storage.
* Notifications.
* Testing.

The main architectural goal is to keep business rules independent from HTTP controllers and database implementation details.

---

# 2. High-Level Architecture

```text
                         ┌──────────────────────┐
                         │      Client Apps     │
                         │ Web / Mobile / Admin │
                         └──────────┬───────────┘
                                    │
                                    ▼
                         ┌──────────────────────┐
                         │     Public Entry     │
                         │     public/index.php │
                         └──────────┬───────────┘
                                    │
                                    ▼
                         ┌──────────────────────┐
                         │       Router         │
                         │       routes/        │
                         └──────────┬───────────┘
                                    │
                  ┌─────────────────┼─────────────────┐
                  │                 │                 │
                  ▼                 ▼                 ▼
             Controllers        Middleware       Request Validation
                  │
                  ▼
             Services
                  │
                  ▼
             Repositories
                  │
                  ▼
             Database
                  │
                  ▼
             MySQL / MariaDB
```

Cross-cutting services:

```text
Authentication
Authorization
Logging
Notifications
Files
Caching
Audit Logging
Email
```

---

# 3. Architectural Layers

MASAR follows a layered architecture.

```text
┌──────────────────────────────────────┐
│ Presentation / HTTP Layer            │
│ Routes + Controllers + Middleware    │
├──────────────────────────────────────┤
│ Application Layer                    │
│ Services + Use Cases                 │
├──────────────────────────────────────┤
│ Domain Layer                         │
│ Business Rules + Entities            │
├──────────────────────────────────────┤
│ Infrastructure Layer                │
│ Database + Files + Email + Cache    │
└──────────────────────────────────────┘
```

Each layer should have a clearly defined responsibility.

---

# 4. Presentation Layer

The presentation layer handles HTTP concerns.

Responsibilities:

* Receive HTTP requests.
* Parse request parameters.
* Validate request structure.
* Authenticate requests.
* Authorize access.
* Call application services.
* Format HTTP responses.

It should **not** contain complex business logic.

Example:

```text
POST /api/trainings
        │
        ▼
Training Route
        │
        ▼
Authentication Middleware
        │
        ▼
Authorization Middleware
        │
        ▼
Training Controller
        │
        ▼
Training Service
```

---

# 5. Routes

Routes are organized under:

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

Each route file represents a functional API domain.

---

# 6. API Router

`routes/api.php` acts as the main API route entry point.

Conceptually:

```text
/api
 │
 ├── auth
 ├── users
 ├── students
 ├── companies
 ├── trainings
 ├── certificates
 ├── messaging
 ├── notifications
 ├── files
 ├── search
 └── admin
```

The router should load the appropriate route modules.

---

# 7. Authentication Architecture

Authentication verifies:

```text
Who is the user?
```

Authorization verifies:

```text
What is the user allowed to do?
```

These are separate concerns.

```text
Request
   │
   ▼
Authentication
   │
   ▼
Authenticated User
   │
   ▼
Authorization
   │
   ▼
Controller
```

---

# 8. Authorization

Authorization should be based on:

* User role.
* Resource ownership.
* Resource state.
* Business rules.

Example:

```text
Student
  └── can apply to training

Company
  └── can manage its own trainings

Admin
  └── can manage administrative resources
```

A company must not be able to modify another company's training simply by changing a URL ID.

---

# 9. Middleware

Middleware provides cross-cutting HTTP behavior.

Typical middleware:

```text
Authentication
Authorization
Rate Limiting
Request ID
CORS
Content Type
Logging
```

Conceptual pipeline:

```text
HTTP Request
     │
     ▼
Request ID
     │
     ▼
CORS
     │
     ▼
Authentication
     │
     ▼
Authorization
     │
     ▼
Controller
```

---

# 10. Controllers

Controllers should remain thin.

A controller should generally:

1. Receive request.
2. Validate input.
3. Call service.
4. Convert result to response.

Example:

```text
TrainingController
        │
        ▼
TrainingService
        │
        ▼
TrainingRepository
```

The controller should not contain SQL queries or large business workflows.

---

# 11. Services

Services contain application/business workflows.

Examples:

```text
AuthService
UserService
StudentService
CompanyService
TrainingService
ApplicationService
CertificateService
MessagingService
NotificationService
PaymentService
FileService
```

Example:

```text
ApplicationService.acceptApplication()
```

may:

```text
1. Validate application.
2. Verify current status.
3. Update application.
4. Create training session.
5. Send notification.
6. Write audit log.
```

This workflow belongs in the service layer.

---

# 12. Repositories

Repositories isolate database access.

Example:

```text
TrainingRepository
```

may provide:

```text
findById()
findPublished()
create()
update()
delete()
search()
```

The service should not need to know how SQL is implemented.

---

# 13. Repository Responsibilities

Repositories should handle:

* Database queries.
* Joins.
* Persistence.
* Query filters.
* Pagination.
* Transactions where appropriate.

Repositories should not decide business policies.

Bad:

```text
TrainingRepository.acceptTraining()
```

Better:

```text
TrainingService.acceptTraining()
```

The repository persists the resulting state.

---

# 14. Domain Layer

The domain layer represents MASAR's business concepts.

Main domains:

```text
Users
Students
Companies
Trainings
Applications
Sessions
Certificates
Appeals
Messaging
Notifications
Payments
Files
```

Business rules should be expressed independently of HTTP.

---

# 15. Dependency Direction

Dependencies should flow inward toward business logic.

```text
HTTP
 │
 ▼
Controllers
 │
 ▼
Services
 │
 ▼
Domain
 │
 ▼
Repositories / Infrastructure
```

Infrastructure details should not leak into the domain unnecessarily.

---

# 16. Database Layer

The database layer consists of:

```text
database/
├── migrations/
├── seeders/
└── schema/
```

Migrations define schema changes.

Seeders provide reference/test data.

`schema/masar.sql` represents the complete schema.

---

# 17. Database Access

Application code should use parameterized queries.

Never concatenate untrusted input into SQL.

Preferred:

```text
SELECT *
FROM training_listings
WHERE company_id = ?
```

with a bound parameter.

---

# 18. Transaction Boundary

Transactions should be managed at the service/use-case level when a business operation contains multiple database changes.

Example:

```text
ApplicationService
       │
       ▼
BEGIN
       │
       ├── update application
       ├── create session
       ├── create notification
       └── create audit log
       │
       ▼
COMMIT
```

This ensures the operation succeeds or fails as one unit.

---

# 19. Shared Layer

Shared code is located under:

```text
shared/
├── enums/
└── functions/
```

Enums contain controlled application states.

Functions contain reusable utility operations.

---

# 20. Enums

Current enum files:

```text
shared/enums/
├── user_roles.php
├── user_statuses.php
├── company_statuses.php
├── training_statuses.php
├── training_types.php
├── training_modes.php
├── payment_types.php
├── application_statuses.php
├── rejection_reasons.php
├── training_session_statuses.php
├── certificate_statuses.php
├── appeal_statuses.php
├── payment_statuses.php
└── notification_types.php
```

These values should remain synchronized with database constraints and business rules.

---

# 21. Shared Functions

Current utility modules:

```text
shared/functions/
├── email.php
├── dates.php
├── ids.php
└── pagination.php
```

These functions should remain generic.

Avoid placing domain-specific business workflows inside shared utilities.

---

# 22. Email Architecture

Email-related helpers are located in:

```text
shared/functions/email.php
```

Email sending should be abstracted behind a service.

Example:

```text
TrainingService
      │
      ▼
NotificationService
      │
      ▼
EmailService
      │
      ▼
SMTP / Email Provider
```

Business services should not directly implement SMTP communication.

---

# 23. Date Architecture

Date utilities are located in:

```text
shared/functions/dates.php
```

All database timestamps should follow one consistent timezone strategy.

Recommended:

```text
Database → UTC
Application → UTC internally
Presentation → localized timezone
```

---

# 24. ID Generation

ID-related helpers belong to:

```text
shared/functions/ids.php
```

Public identifiers should be generated consistently.

Avoid exposing internal sequential IDs when doing so creates unnecessary enumeration risk.

---

# 25. Pagination Architecture

Pagination helpers belong to:

```text
shared/functions/pagination.php
```

Pagination should be standardized across API endpoints.

Example response:

```json
{
  "data": [],
  "pagination": {
    "page": 1,
    "per_page": 20,
    "total": 100,
    "total_pages": 5
  }
}
```

The exact response format should remain consistent across the API.

---

# 26. File Architecture

Files are separated into:

```text
Database metadata
        +
Physical storage
```

Database:

```text
files
```

Filesystem:

```text
storage/uploads/
storage/certificates/
```

The API should expose controlled download endpoints rather than direct filesystem access.

---

# 27. File Upload Flow

```text
Client
  │
  ▼
POST /files
  │
  ▼
Authentication
  │
  ▼
File Validation
  │
  ├── MIME validation
  ├── Size validation
  └── Extension validation
  │
  ▼
Temporary Storage
  │
  ▼
Virus/Malware Scan if supported
  │
  ▼
Permanent Storage
  │
  ▼
Create files record
  │
  ▼
Response
```

---

# 28. File Security

Uploaded files must not automatically become executable.

Storage directories should be configured so uploaded content cannot execute server-side code.

User-controlled filenames should never be trusted as storage paths.

Generate safe storage keys.

---

# 29. Certificate Architecture

Certificate generation follows:

```text
Training Session
      │
      ▼
Completion
      │
      ▼
Certificate Service
      │
      ├── Generate certificate number
      ├── Generate certificate document
      ├── Store file
      ├── Create database record
      └── Notify student
```

Certificate generation should happen only after the required business conditions are satisfied.

---

# 30. Certificate Verification

If public verification is supported:

```text
Public Verification Request
        │
        ▼
Verification Code
        │
        ▼
Certificate Service
        │
        ▼
Certificate Record
        │
        ▼
Status / Validity
```

Private student information should not be exposed unnecessarily.

---

# 31. Messaging Architecture

Messaging is separated into:

```text
Conversations
Messages
Files
Notifications
```

Flow:

```text
User A
  │
  ▼
Conversation
  │
  ▼
Message
  │
  ├── optional attachment
  │
  ▼
User B
  │
  ▼
Notification
```

---

# 32. Message Authorization

Every messaging request must verify conversation membership.

Example:

```text
GET /conversations/10/messages
```

requires:

```text
Authenticated User
        │
        ▼
Is user participant in conversation 10?
        │
     ┌──┴──┐
    YES    NO
     │      │
     ▼      ▼
 Continue  403
```

---

# 33. Notification Architecture

Notifications can originate from domain events.

Examples:

```text
Application Accepted
Application Rejected
Training Published
Training Expiring
Certificate Issued
Certificate Appeal Updated
Payment Completed
```

Conceptually:

```text
Domain Event
     │
     ▼
Notification Service
     │
     ├── Database Notification
     │
     └── Email Notification
```

---

# 34. Background Jobs

Scheduled jobs are located under:

```text
cron/
├── close_expired_trainings.php
├── expire_trial_periods.php
├── send_expiry_notifications.php
└── cleanup_temp_files.php
```

These jobs handle tasks that should not depend on a user request.

---

# 35. Expired Training Job

```text
cron/close_expired_trainings.php
```

Conceptually:

```text
Find published trainings
        │
        ▼
application_deadline < now
        │
        ▼
Close training
        │
        ▼
Write audit log
```

The operation should be idempotent.

Running it twice should not corrupt data.

---

# 36. Trial Expiration Job

```text
cron/expire_trial_periods.php
```

handles expiration of applicable trial periods.

The job should:

1. Find expired trials.
2. Verify current state.
3. Update status.
4. Create required notifications/audit records.
5. Avoid repeating completed transitions.

---

# 37. Expiry Notifications

```text
cron/send_expiry_notifications.php
```

should identify resources approaching expiration and notify the appropriate users.

To avoid duplicate notifications, the implementation should maintain a reliable idempotency mechanism.

---

# 38. Temporary File Cleanup

```text
cron/cleanup_temp_files.php
```

removes expired temporary uploads.

Example:

```text
storage/uploads/temp/
```

The job should never remove active files.

---

# 39. Cron Security

Cron scripts should not be exposed as public HTTP endpoints.

They should be executed by the server scheduler.

Example:

```text
Linux cron
   │
   ▼
php cron/cleanup_temp_files.php
```

The scripts should load the same application configuration/bootstrap as required by the project.

---

# 40. Logging

Logs are stored under:

```text
storage/logs/
```

Logs should contain useful diagnostic information without exposing:

```text
passwords
tokens
secrets
full payment credentials
```

---

# 41. Cache

Cache files are stored under:

```text
storage/cache/
```

Cache should only contain data that can safely be regenerated.

Never treat cache as the source of truth for critical business state.

---

# 42. Public Entry Point

The application entry point is:

```text
public/index.php
```

All normal HTTP requests should flow through the public entry point.

Conceptually:

```text
Browser
   │
   ▼
Web Server
   │
   ▼
public/index.php
   │
   ▼
Application Bootstrap
   │
   ▼
Router
```

---

# 43. Apache Configuration

```text
public/.htaccess
```

handles URL rewriting and web-server-specific routing behavior when Apache is used.

The goal is to route application requests to:

```text
public/index.php
```

while preventing direct access to internal application directories.

---

# 44. Public Directory Security

Only the following should normally be web-accessible:

```text
public/
```

Directories such as:

```text
app/
config/
database/
routes/
shared/
storage/
tests/
docs/
```

should not be directly accessible from the public web root.

---

# 45. Configuration

Configuration should be centralized.

Typical configuration areas:

```text
Database
Authentication
Mail
Storage
Application
Logging
Cache
External services
```

Secrets should be supplied through environment variables or secure deployment configuration.

---

# 46. Environment Configuration

Different environments should have independent configuration:

```text
development
testing
staging
production
```

Never commit production secrets to source control.

---

# 47. API Response Architecture

API responses should follow a consistent structure.

Success:

```json
{
  "success": true,
  "data": {}
}
```

Error:

```json
{
  "success": false,
  "message": "Validation failed.",
  "errors": {}
}
```

The exact response contract should be defined consistently across API documentation.

---

# 48. HTTP Status Codes

Use appropriate HTTP status codes.

Examples:

```text
200 OK
201 Created
204 No Content

400 Bad Request
401 Unauthorized
403 Forbidden
404 Not Found
409 Conflict
422 Unprocessable Entity
429 Too Many Requests

500 Internal Server Error
```

---

# 49. Error Handling

Application exceptions should be converted into safe API responses.

Architecture:

```text
Exception
   │
   ▼
Global Error Handler
   │
   ├── Log technical details
   │
   └── Return safe client response
```

Do not expose stack traces in production.

---

# 50. Validation Architecture

Validation should occur before service execution.

```text
Request
  │
  ▼
Validator
  │
  ├── invalid → 422
  │
  └── valid
        │
        ▼
     Service
```

Business validation may still be required inside the service.

---

# 51. Business Validation

Request validation answers:

```text
Is this input structurally valid?
```

Business validation answers:

```text
Is this operation allowed right now?
```

Example:

```text
application_id = 10
```

may be structurally valid, but accepting it could still be invalid if:

```text
application.status = rejected
```

---

# 52. Search Architecture

Search endpoints should be isolated under:

```text
routes/search.php
```

Search flow:

```text
Request
  │
  ▼
Search Controller
  │
  ▼
Search Service
  │
  ▼
Repository
  │
  ▼
Database
```

Search logic should not be duplicated across controllers.

---

# 53. Admin Architecture

Administrative routes are located under:

```text
routes/admin.php
```

Admin requests should pass through:

```text
Authentication
      │
      ▼
Admin Authorization
      │
      ▼
Admin Controller
      │
      ▼
Admin Service
```

Every sensitive admin action should be auditable.

---

# 54. Domain Events

Domain events can be used to decouple secondary actions from core business operations.

Example:

```text
Application Accepted
        │
        ├── Create Session
        ├── Send Notification
        ├── Send Email
        └── Write Audit Log
```

The implementation may initially perform these synchronously.

A queue/event system can be introduced later without changing the core business rule.

---

# 55. Queue Readiness

The architecture should avoid tightly coupling business logic to immediate email/file/notification execution.

Instead of:

```text
ApplicationService
    ↓
SMTP implementation
```

prefer:

```text
ApplicationService
    ↓
NotificationService
    ↓
Email Provider
```

This makes future asynchronous processing easier.

---

# 56. Caching Strategy

Potential cache candidates:

```text
universities
faculties
degrees
specializations
skills
```

These are mostly reference data.

Do not cache highly volatile business state unless invalidation is well defined.

---

# 57. Cache Invalidation

Every cached resource must define:

```text
cache key
expiration
invalidation event
fallback behavior
```

If cache fails, the application should normally fall back to the database.

---

# 58. Performance Strategy

Performance should be improved in this order:

```text
1. Correct queries
2. Proper indexes
3. Pagination
4. Avoid N+1 queries
5. Caching
6. Background jobs
7. Specialized infrastructure
```

Do not prematurely introduce complex infrastructure.

---

# 59. N+1 Prevention

Example bad pattern:

```text
Get 100 trainings
        │
        ├── query company
        ├── query company
        ├── query company
        └── ...
```

Preferred:

```text
Get trainings
      +
Fetch required companies efficiently
```

through appropriate joins or batched queries.

---

# 60. Security Architecture

Security must exist at multiple layers:

```text
Web Server
   ↓
HTTP Layer
   ↓
Authentication
   ↓
Authorization
   ↓
Validation
   ↓
Business Rules
   ↓
Database Constraints
   ↓
Audit Logging
```

No single layer should be treated as the complete security boundary.

---

# 61. Authentication Security

Authentication should include:

* Secure password hashing.
* Session/token protection.
* Login throttling.
* Secure logout.
* Credential validation.
* Appropriate token expiration.

The exact authentication mechanism should follow the implementation documented in:

```text
docs/api/authentication.md
```

---

# 62. Authorization Security

Authorization must verify both:

```text
Role
+
Resource ownership
```

Example:

```text
Company A
   │
   └── Training 100

Company B
   │
   └── Request edit Training 100
```

The request must be rejected even if Company B has the correct general role.

---

# 63. Rate Limiting

Rate limiting should protect sensitive endpoints such as:

```text
login
password reset
file upload
search
messaging
public certificate verification
```

Limits should be configurable.

---

# 64. CSRF

If browser authentication uses cookies, state-changing requests should be protected against CSRF.

If the API uses a stateless authorization mechanism, CSRF requirements depend on the exact authentication implementation.

---

# 65. CORS

CORS should be explicitly configured.

Production should not use:

```text
Access-Control-Allow-Origin: *
```

for authenticated APIs unless the security model explicitly permits it.

---

# 66. File Upload Security

File uploads should validate:

```text
file size
MIME type
extension
content
storage destination
authorization
```

Do not trust only the filename extension.

---

# 67. SQL Security

Database access must use:

```text
prepared statements
parameterized queries
```

Never concatenate user input into SQL.

---

# 68. Audit Architecture

Audit logging should be centralized.

Example:

```text
AuditService
    │
    ▼
audit_logs
```

Business services can call:

```text
AuditService.record(...)
```

rather than implementing audit SQL independently.

---

# 69. Observability

The system should provide:

```text
Application Logs
Audit Logs
Error Logs
Database Monitoring
Cron Monitoring
```

Where possible, requests should have a unique request ID.

Example:

```text
Request ID:
req_01HXYZ...
```

This allows logs from one request to be correlated.

---

# 70. Testing Architecture

Tests are divided into:

```text
tests/
├── unit/
└── integration/
```

Unit tests verify isolated components.

Integration tests verify interactions between components and infrastructure.

---

# 71. Unit Tests

Unit tests should cover:

```text
Services
Validators
Business rules
Utility functions
Status transitions
ID generation
Date utilities
Pagination
```

Example:

```text
tests/unit/training/
tests/unit/certificates/
```

---

# 72. Integration Tests

Integration tests should cover complete workflows.

Examples:

```text
User registration
Login
Student application
Company training creation
Application acceptance
Certificate issuance
Admin actions
```

---

# 73. Deployment Architecture

Production deployment conceptually:

```text
                 Internet
                    │
                    ▼
              Reverse Proxy
                    │
                    ▼
               Web Server
                    │
                    ▼
              public/index.php
                    │
        ┌───────────┼───────────┐
        ▼           ▼           ▼
    Application   Database    Storage
```

External services may include:

```text
Email Provider
Payment Provider
Object Storage
Monitoring
```

depending on deployment requirements.

---

# 74. Production Filesystem

The web server should expose only:

```text
public/
```

Application data should remain outside the public document root.

Storage directories should have controlled permissions.

---

# 75. Background Scheduler

Production cron configuration should execute:

```text
close_expired_trainings.php
expire_trial_periods.php
send_expiry_notifications.php
cleanup_temp_files.php
```

at appropriate intervals.

Example conceptual schedule:

```text
Every hour:
    close expired trainings

Every hour:
    expire trial periods

Daily/hourly:
    send expiry notifications

Every few minutes/hour:
    cleanup temporary files
```

Actual schedules depend on business requirements.

---

# 76. Architecture Dependency Graph

```text
                    CLIENT
                      │
                      ▼
                 PUBLIC ENTRY
                      │
                      ▼
                    ROUTER
                      │
             ┌────────┴────────┐
             ▼                 ▼
        MIDDLEWARE         CONTROLLERS
             │                 │
             └────────┬────────┘
                      ▼
                   SERVICES
                      │
          ┌───────────┼────────────┐
          ▼           ▼            ▼
      DOMAIN      REPOSITORIES   SHARED
                      │
                      ▼
                   DATABASE


SERVICES
   │
   ├── Notification Service ──► Email
   │
   ├── File Service ──────────► Storage
   │
   ├── Payment Service ───────► Payment Provider
   │
   └── Audit Service ─────────► Audit Logs


CRON
 │
 └──► SERVICES
```

---

# 77. Core Architectural Rule

The most important rule is:

```text
Controllers handle HTTP.
Services handle business workflows.
Repositories handle persistence.
Database enforces integrity.
Shared utilities provide reusable infrastructure.
```

Avoid mixing these responsibilities.

---

# 78. Example: Create Training

Complete flow:

```text
POST /api/trainings
        │
        ▼
Router
        │
        ▼
Authentication
        │
        ▼
Company Authorization
        │
        ▼
Request Validation
        │
        ▼
TrainingController
        │
        ▼
TrainingService
        │
        ├── validate company
        ├── validate dates
        ├── validate status
        ├── create training
        ├── attach specializations
        ├── attach skills
        └── audit action
        │
        ▼
TrainingRepository
        │
        ▼
Database
```

---

# 79. Example: Apply to Training

```text
POST /api/trainings/{id}/applications
        │
        ▼
Authentication
        │
        ▼
Student Authorization
        │
        ▼
ApplicationController
        │
        ▼
ApplicationService
        │
        ├── verify training exists
        ├── verify training is open
        ├── verify deadline
        ├── verify student eligibility
        ├── prevent duplicate application
        └── create application
        │
        ▼
Database
        │
        ▼
Notification / Audit
```

---

# 80. Example: Accept Application

```text
Admin/Company Request
        │
        ▼
Authentication
        │
        ▼
Authorization
        │
        ▼
ApplicationService
        │
        ▼
BEGIN TRANSACTION
        │
        ├── lock application
        ├── verify current status
        ├── update application
        ├── create session
        ├── create notification
        └── create audit log
        │
        ▼
COMMIT
```

---

# 81. Example: Issue Certificate

```text
Training Session
        │
        ▼
Verify completion
        │
        ▼
CertificateService
        │
        ├── generate certificate ID
        ├── generate certificate file
        ├── store file
        ├── create certificate record
        ├── create audit record
        └── notify student
        │
        ▼
Certificate
```

---

# 82. Example: Send Message

```text
POST /api/conversations/{id}/messages
        │
        ▼
Authentication
        │
        ▼
Conversation Membership Check
        │
        ▼
Message Validation
        │
        ▼
MessagingService
        │
        ├── create message
        ├── attach files if valid
        └── create notification
        │
        ▼
Response
```

---

# 83. Example: Admin Action

```text
Admin Request
      │
      ▼
Authentication
      │
      ▼
Admin Authorization
      │
      ▼
Validation
      │
      ▼
Admin Service
      │
      ├── business operation
      └── audit log
      │
      ▼
Response
```

Every sensitive administrative mutation should produce an audit record.

---

# 84. Scalability Strategy

The initial architecture should remain simple.

Recommended evolution:

```text
Phase 1
Monolithic modular application
        │
        ▼
Phase 2
Caching + queues + optimized DB
        │
        ▼
Phase 3
Dedicated infrastructure where required
```

Do not split the application into microservices before actual scale or organizational requirements justify it.

---

# 85. Modular Monolith

MASAR is best treated initially as a modular monolith.

```text
                 MASAR APPLICATION
┌───────────────────────────────────────────┐
│                                           │
│ Auth   Users   Students   Companies       │
│                                           │
│ Trainings   Applications   Certificates   │
│                                           │
│ Messaging   Notifications   Files         │
│                                           │
│ Payments   Admin   Search                 │
│                                           │
└───────────────────────────────────────────┘
```

Modules share infrastructure while keeping their business responsibilities separated.

---

# 86. Why Modular Monolith

This approach provides:

* Simpler deployment.
* Lower infrastructure complexity.
* Easier transactions.
* Easier local development.
* Easier testing.
* Centralized authorization.
* Centralized database consistency.

Individual modules can later be extracted if there is a real need.

---

# 87. Module Boundaries

The following boundaries should remain clear:

```text
Auth
Users
Students
Companies
Training
Applications
Certificates
Messaging
Notifications
Files
Payments
Admin
Search
```

A module should interact with another module through services/use cases rather than directly manipulating its internal implementation.

---

# 88. Cross-Module Communication

Example:

```text
Application Module
       │
       ▼
Certificate Module
```

The application module should not directly modify certificate tables.

Instead:

```text
ApplicationService
       │
       ▼
CertificateService
```

when the business workflow requires it.

---

# 89. Business Rules Location

Business rules should be documented in:

```text
docs/business_rules.md
```

Implementation should reside in services/domain logic.

Documentation and implementation must remain synchronized.

---

# 90. API Documentation

API contracts are documented under:

```text
docs/api/
```

including:

```text
authentication.md
users.md
students.md
companies.md
trainings.md
applications.md
certificates.md
messaging.md
notifications.md
files.md
search.md
admin.md
```

Each API document should define:

```text
endpoint
method
authentication
authorization
request
validation
response
errors
business rules
```

---

# 91. Architecture Documentation

The architecture documentation should be updated when there are major changes to:

* Module boundaries.
* Database architecture.
* Authentication.
* Storage.
* Background jobs.
* External integrations.
* Deployment model.

---

# 92. Final Architecture Principles

MASAR should follow these architectural principles:

1. **Separation of concerns.**
2. **Thin controllers.**
3. **Business logic in services/domain layer.**
4. **Database access through repositories/data-access components.**
5. **Strong database integrity.**
6. **Centralized authorization.**
7. **Consistent validation.**
8. **Transactional critical workflows.**
9. **Auditable sensitive operations.**
10. **Secure file handling.**
11. **Idempotent scheduled jobs.**
12. **Consistent API contracts.**
13. **Modular monolith as the initial architecture.**
14. **Infrastructure should remain replaceable.**
15. **Security must be enforced at multiple layers.**
16. **Documentation must remain synchronized with implementation.**

---

# 93. Final System View

```text
                              MASAR
                                │
               ┌────────────────┴────────────────┐
               │                                 │
          HTTP / API                         CRON JOBS
               │                                 │
               ▼                                 ▼
            ROUTES                           SCHEDULED TASKS
               │                                 │
               ▼                                 │
          MIDDLEWARE                             │
               │                                 │
               ▼                                 │
         CONTROLLERS                              │
               │                                 │
               └──────────────┬──────────────────┘
                              ▼
                           SERVICES
                              │
          ┌───────────────────┼────────────────────┐
          │                   │                    │
          ▼                   ▼                    ▼
       DOMAIN            REPOSITORIES          SHARED
          │                   │                    │
          │                   ▼                    ├── Enums
          │                DATABASE                └── Functions
          │                   │
          │                   ▼
          │              MySQL/MariaDB
          │
          ├── Notifications ──► Email
          │
          ├── Files ───────────► Storage
          │
          ├── Payments ────────► Payment Provider
          │
          └── Audit ───────────► Audit Logs
```

This architecture provides a clean foundation for implementing MASAR as a secure, maintainable, testable, and scalable modular PHP application.

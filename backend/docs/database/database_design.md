# MASAR Database Design

## 1. Overview

This document defines the database design principles, structure, constraints, relationships, indexing strategy, and data integrity rules for the MASAR platform.

The database is a relational database designed to support:

* User authentication and authorization.
* Student profiles.
* Company profiles.
* Academic information.
* Training listings.
* Training applications.
* Training sessions.
* Certificates.
* Certificate appeals.
* Messaging.
* Notifications.
* File management.
* Payments.
* Audit logging.

The database implementation is defined through:

```text
database/migrations/
database/schema/masar.sql
```

---

# 2. Database Architecture

The database is divided logically into several domains.

```text
┌─────────────────────────────────────────────┐
│                 MASAR DB                    │
├─────────────────────────────────────────────┤
│ Identity                                    │
│ ├── users                                   │
│ ├── students                                │
│ └── companies                               │
├─────────────────────────────────────────────┤
│ Academic                                    │
│ ├── universities                            │
│ ├── faculties                               │
│ ├── degrees                                 │
│ ├── specializations                          │
│ ├── skills                                  │
│ └── student_skills                           │
├─────────────────────────────────────────────┤
│ Training                                    │
│ ├── training_listings                       │
│ ├── training_specializations                │
│ ├── training_skills                         │
│ ├── training_applications                   │
│ └── training_sessions                       │
├─────────────────────────────────────────────┤
│ Certification                               │
│ ├── certificates                            │
│ └── certificate_appeals                     │
├─────────────────────────────────────────────┤
│ Communication                               │
│ ├── conversations                            │
│ └── messages                                │
├─────────────────────────────────────────────┤
│ Platform Services                           │
│ ├── notifications                            │
│ ├── files                                   │
│ ├── payments                                │
│ └── audit_logs                              │
└─────────────────────────────────────────────┘
```

---

# 3. Database Naming Convention

Table names use lowercase `snake_case`.

Examples:

```text
users
training_listings
training_applications
certificate_appeals
audit_logs
```

Column names also use lowercase `snake_case`.

Examples:

```text
user_id
created_at
updated_at
training_id
application_id
```

Foreign keys should follow:

```text
<table_singular>_id
```

Examples:

```text
user_id
student_id
company_id
training_id
certificate_id
```

---

# 4. Primary Keys

Every main entity table should have a primary key.

Recommended:

```text
id
```

Example:

```text
users.id
students.id
companies.id
training_listings.id
certificates.id
```

The exact ID implementation may use:

* Integer IDs.
* BIGINT IDs.
* UUIDs.

The implementation must remain consistent throughout the database.

---

# 5. Foreign Keys

Foreign keys enforce relationships between entities.

Examples:

```text
students.user_id
companies.user_id
faculties.university_id
degrees.faculty_id
training_listings.company_id
training_applications.student_id
training_applications.training_id
training_sessions.application_id
certificates.session_id
certificate_appeals.certificate_id
messages.conversation_id
messages.sender_id
notifications.user_id
payments.user_id
audit_logs.user_id
```

Foreign keys should reference valid primary keys.

---

# 6. Users Table

```text
users
```

is the central identity table.

Typical logical fields:

```text
id
name
email
password_hash
role
status
created_at
updated_at
```

Sensitive authentication fields must never be exposed through API responses.

---

# 7. User Email

Email should normally be unique:

```text
UNIQUE(email)
```

The application should normalize email addresses consistently before storage and comparison.

Example normalization:

```text
Ahmed@Example.COM
```

becomes:

```text
ahmed@example.com
```

if that is the application's chosen policy.

---

# 8. User Status

User status values must come from:

```text
shared/enums/user_statuses.php
```

The database should reject values outside the supported state set when practical.

Typical states may include:

```text
active
pending
blocked
suspended
```

The actual list must match the project implementation.

---

# 9. User Roles

Roles are defined in:

```text
shared/enums/user_roles.php
```

Typical roles include:

```text
student
company
admin
```

The database should not rely exclusively on application-level validation if a database constraint can safely enforce the allowed values.

---

# 10. Students Table

```text
students
```

contains student-specific data.

Relationship:

```text
users 1 ───── 0..1 students
```

`students.user_id` should reference:

```text
users.id
```

Recommended unique constraint:

```text
UNIQUE(user_id)
```

This prevents multiple student profiles from belonging to the same user.

---

# 11. Companies Table

```text
companies
```

contains company-specific data.

Relationship:

```text
users 1 ───── 0..1 companies
```

Recommended:

```text
UNIQUE(user_id)
```

if the business model supports one company profile per user.

---

# 12. Universities

```text
universities
```

is reference/master data.

Relationship:

```text
universities
    │
    └──< faculties
```

A university can have many faculties.

---

# 13. Faculties

```text
faculties
```

belongs to one university.

```text
faculties.university_id
        ↓
universities.id
```

Recommended index:

```text
INDEX(university_id)
```

---

# 14. Degrees

```text
degrees
```

belongs to a faculty.

```text
degrees.faculty_id
        ↓
faculties.id
```

Recommended index:

```text
INDEX(faculty_id)
```

---

# 15. Specializations

```text
specializations
```

contains reusable specialization records.

Specializations can participate in multiple domains:

```text
students
companies
trainings
```

Many-to-many relationships should use dedicated junction tables.

---

# 16. Skills

```text
skills
```

contains reusable skill definitions.

Examples:

```text
PHP
JavaScript
SQL
Communication
Problem Solving
```

The exact seed data belongs in:

```text
database/seeders/skills_seeder.php
```

---

# 17. Student Skills

```text
student_skills
```

is a junction table.

Structure conceptually:

```text
student_id
skill_id
```

Relationship:

```text
students N ───── M skills
```

through:

```text
student_skills
```

Recommended constraint:

```text
UNIQUE(student_id, skill_id)
```

This prevents duplicate skill assignments.

---

# 18. Company Specializations

```text
company_specializations
```

connects:

```text
companies
specializations
```

Recommended:

```text
UNIQUE(company_id, specialization_id)
```

and indexes on both foreign keys.

---

# 19. Training Listings

```text
training_listings
```

is the central training opportunity table.

Each training belongs to a company:

```text
companies 1 ───── N training_listings
```

Typical fields may include:

```text
id
company_id
title
description
training_type
training_mode
status
start_date
end_date
application_deadline
created_at
updated_at
```

The exact fields must follow:

```text
011_create_training_listings_table.sql
```

---

# 20. Training Status

Training status values must come from:

```text
shared/enums/training_statuses.php
```

The status should be treated as a state machine rather than an arbitrary string.

Example:

```text
draft
  ↓
published
  ↓
closed
  ↓
expired
```

The actual transitions must follow the business rules.

---

# 21. Training Types

Training types are defined in:

```text
shared/enums/training_types.php
```

Examples might include:

```text
internship
training
summer_training
```

The final values must match the implementation.

---

# 22. Training Modes

Training modes are defined in:

```text
shared/enums/training_modes.php
```

Possible examples:

```text
onsite
remote
hybrid
```

Again, the actual enum values are authoritative.

---

# 23. Training Specializations

```text
training_specializations
```

connects:

```text
training_listings
specializations
```

Recommended:

```text
UNIQUE(training_id, specialization_id)
```

---

# 24. Training Skills

```text
training_skills
```

connects:

```text
training_listings
skills
```

Recommended:

```text
UNIQUE(training_id, skill_id)
```

---

# 25. Training Applications

```text
training_applications
```

represents applications submitted by students.

Relationships:

```text
students 1 ───── N training_applications

training_listings 1 ───── N training_applications
```

Logical structure:

```text
student
   │
   ▼
application
   │
   ▼
training
```

---

# 26. Application Uniqueness

If a student is allowed only one application per training:

```text
UNIQUE(student_id, training_id)
```

This is preferable to relying only on application code because concurrent requests could otherwise create duplicates.

---

# 27. Application Status

Application status values are defined in:

```text
shared/enums/application_statuses.php
```

Possible lifecycle:

```text
pending
   │
   ├── accepted
   │
   ├── rejected
   │
   └── withdrawn
```

The actual values and transitions must match the project requirements.

---

# 28. Rejection Reasons

Rejection reasons may be represented by seeded reference data.

Seeder:

```text
database/seeders/rejection_reasons_seeder.php
```

If applications reference rejection reasons:

```text
training_applications.rejection_reason_id
              ↓
rejection_reasons.id
```

The relationship should use a foreign key.

---

# 29. Training Sessions

```text
training_sessions
```

represents actual training participation.

Typical relationship:

```text
training_applications 1 ───── 0..1 training_sessions
```

if one accepted application creates one training session.

The database design must prevent impossible sessions, such as a session created from a permanently rejected application, unless explicitly supported.

---

# 30. Session Status

Status values are defined in:

```text
shared/enums/training_session_statuses.php
```

Possible lifecycle:

```text
scheduled
   ↓
active
   ↓
completed
```

Other states may include cancellation or termination.

---

# 31. Certificates

```text
certificates
```

represents certificates issued after successful training.

Logical relationship:

```text
training_sessions
       │
       ▼
certificates
```

A certificate should have a stable identifier suitable for verification.

---

# 32. Certificate Status

Values are defined in:

```text
shared/enums/certificate_statuses.php
```

Possible states:

```text
issued
revoked
```

The actual values must match the implementation.

Certificates should normally be retained even after revocation for audit and verification purposes.

---

# 33. Certificate Appeals

```text
certificate_appeals
```

references certificates:

```text
certificate_appeals.certificate_id
              ↓
certificates.id
```

Status values are defined in:

```text
shared/enums/appeal_statuses.php
```

Typical flow:

```text
submitted
   ↓
under_review
   ↓
approved / rejected
```

The actual lifecycle must follow the business rules.

---

# 34. Conversations

```text
conversations
```

represents messaging threads.

A conversation may contain:

```text
id
created_at
updated_at
```

and participant information according to the final messaging schema.

If multiple participants are supported, a participant junction table is recommended.

---

# 35. Messages

```text
messages
```

belongs to a conversation.

Typical relationships:

```text
conversations 1 ───── N messages

users 1 ───── N messages
```

Typical fields:

```text
id
conversation_id
sender_id
body
created_at
```

Messages should be immutable or have a clearly defined edit/delete policy.

---

# 36. Message Authorization

A user must only access messages belonging to conversations in which they are a participant.

The following must never be sufficient:

```text
GET /api/messages/{message_id}
```

Authorization must verify ownership/membership.

---

# 37. Notifications

```text
notifications
```

belongs to a user.

Typical fields:

```text
id
user_id
type
title
body
read_at
created_at
```

Notification type values come from:

```text
shared/enums/notification_types.php
```

An index should exist on:

```text
user_id
```

and preferably:

```text
(user_id, read_at)
```

for unread-notification queries.

---

# 38. Files

```text
files
```

stores metadata about uploaded files.

The physical file should not be considered the database record itself.

Logical separation:

```text
Database
    ↓
files
    ↓
metadata / ownership / storage key

Filesystem
    ↓
storage/uploads/...
    ↓
actual file
```

---

# 39. File Storage

Storage directories include:

```text
storage/
├── uploads/
│   ├── cvs/
│   └── temp/
└── certificates/
```

The database should store a safe storage identifier/path rather than exposing internal filesystem paths directly to clients.

---

# 40. Temporary Files

Temporary files must have a cleanup strategy.

The scheduled job:

```text
cron/cleanup_temp_files.php
```

should remove expired temporary files.

Database records associated with temporary files should also be handled consistently.

---

# 41. Payments

```text
payments
```

stores payment transactions.

Possible logical fields:

```text
id
user_id
amount
currency
payment_type
status
provider_reference
created_at
updated_at
```

The actual fields must match:

```text
022_create_payments_table.sql
```

---

# 42. Payment Types

Defined by:

```text
shared/enums/payment_types.php
```

Payment types should describe the business purpose of the transaction.

---

# 43. Payment Status

Defined by:

```text
shared/enums/payment_statuses.php
```

Possible lifecycle:

```text
pending
   ↓
completed
```

or:

```text
pending
   ↓
failed
```

Refund states should be added only if supported by the business model.

---

# 44. Payment Idempotency

Payment-related operations must protect against duplicate processing.

A provider transaction/reference identifier should be unique when applicable.

Example:

```text
UNIQUE(provider_reference)
```

This prevents the same provider transaction from being processed twice.

---

# 45. Audit Logs

```text
audit_logs
```

records security-sensitive and business-critical actions.

Typical fields:

```text
id
user_id
action
entity_type
entity_id
old_values
new_values
ip_address
user_agent
created_at
```

The final fields must match the migration.

---

# 46. Audit Log Immutability

Audit logs should not normally be updated or deleted through application APIs.

Preferred model:

```text
INSERT → immutable record
```

rather than:

```text
INSERT → UPDATE → DELETE
```

This protects the integrity of the audit trail.

---

# 47. Timestamps

Entity tables should normally contain:

```text
created_at
updated_at
```

where modification tracking is required.

Some immutable/event-style tables may only require:

```text
created_at
```

Examples:

```text
audit_logs
notifications
messages
```

depending on implementation.

---

# 48. Soft Deletion

Soft deletion may be used where historical relationships must remain available.

Typical implementation:

```text
deleted_at
```

Instead of:

```text
DELETE FROM ...
```

Soft deletion is particularly useful for:

```text
users
companies
trainings
reference data
```

when historical records must remain intact.

---

# 49. Hard Deletion

Physical deletion should be reserved for data that can safely be removed.

Examples:

```text
expired temporary files
temporary records
unreferenced technical data
```

Destructive operations must respect foreign-key relationships.

---

# 50. Referential Actions

Foreign keys should explicitly define their intended behavior.

Possible actions:

```text
ON DELETE RESTRICT
ON DELETE CASCADE
ON DELETE SET NULL
```

Recommended general principle:

### CASCADE

Use for pure junction/child records where deletion of the parent logically removes the relationship.

Example:

```text
student
   ↓
student_skills
```

### RESTRICT

Use for important historical domain records.

Example:

```text
certificate
   ↓
certificate_appeals
```

### SET NULL

Use where the relationship is optional and historical data should remain.

The final migration must define the exact behavior.

---

# 51. Normalization

The database should follow relational normalization principles.

Avoid storing repeated information.

Bad:

```text
training
required_skills = "PHP, MySQL, Git"
```

Preferred:

```text
skills
training_skills
```

Similarly:

```text
training_specializations
student_skills
company_specializations
```

are used to represent many-to-many relationships.

---

# 52. Denormalization

Denormalization should only be introduced for measured performance requirements.

Do not duplicate critical domain information merely for convenience.

If duplicated data is necessary, define:

```text
source of truth
synchronization rule
update strategy
```

---

# 53. Transactions

Operations involving multiple related writes should use database transactions.

Example: accepting an application:

```text
BEGIN
   ↓
Update application
   ↓
Create training session
   ↓
Create notification
   ↓
Create audit log
   ↓
COMMIT
```

If a required operation fails:

```text
ROLLBACK
```

---

# 54. Example Transaction

```text
BEGIN TRANSACTION

1. Lock application.
2. Verify application status.
3. Change status to accepted.
4. Create training session.
5. Create notification.
6. Create audit log.

COMMIT
```

This prevents inconsistent states.

---

# 55. Row Locking

Concurrent operations involving critical state changes should use appropriate row locking.

Example:

```text
Admin A accepts application
Admin B rejects application
```

The system must ensure only one valid transition succeeds.

---

# 56. Status State Machines

Statuses should not be treated as arbitrary labels.

Every important status field should define:

```text
allowed states
allowed transitions
terminal states
required side effects
```

For example:

```text
pending
   ├── accepted
   ├── rejected
   └── withdrawn
```

A transition from:

```text
rejected → accepted
```

should be rejected unless explicitly supported.

---

# 57. Data Validation

Application validation should occur before database writes.

Examples:

```text
email format
required fields
foreign key existence
date ranges
status transitions
duplicate relationships
numeric ranges
file constraints
```

Database constraints provide a second layer of protection.

---

# 58. Database Constraints

Important constraints include:

```text
PRIMARY KEY
FOREIGN KEY
UNIQUE
NOT NULL
CHECK
```

where supported and appropriate.

Application-level validation should never be the only protection for critical integrity rules.

---

# 59. Indexing Strategy

Indexes should be created based on:

* Foreign-key lookups.
* Search filters.
* Sorting.
* Unique constraints.
* Frequent joins.
* Status filtering.
* Date filtering.

Do not create indexes indiscriminately because indexes increase write cost and storage usage.

---

# 60. Important Indexes

Recommended indexes include:

```text
users:
    email
    role
    status

students:
    user_id

companies:
    user_id
    status

faculties:
    university_id

degrees:
    faculty_id

student_skills:
    student_id
    skill_id

company_specializations:
    company_id
    specialization_id

training_listings:
    company_id
    status
    application_deadline

training_specializations:
    training_id
    specialization_id

training_skills:
    training_id
    skill_id

training_applications:
    student_id
    training_id
    status

training_sessions:
    application_id
    status

certificates:
    session_id
    status

certificate_appeals:
    certificate_id
    status

messages:
    conversation_id
    sender_id

notifications:
    user_id
    created_at
    read_at

files:
    owner_id
    created_at

payments:
    user_id
    status
    provider_reference

audit_logs:
    user_id
    entity_type
    entity_id
    created_at
```

The actual indexes should be verified against production query patterns.

---

# 61. Composite Indexes

Composite indexes should be used where queries consistently filter by multiple columns.

Examples:

```text
training_applications(student_id, training_id)

notifications(user_id, read_at)

audit_logs(entity_type, entity_id)

training_listings(company_id, status)
```

Column order should follow actual query patterns.

---

# 62. Pagination

Large tables must be paginated.

Examples:

```text
users
training_listings
training_applications
messages
notifications
audit_logs
payments
```

The API should enforce a maximum page size.

Example:

```text
per_page <= 100
```

The actual maximum can be configured by the application.

---

# 63. Offset vs Cursor Pagination

Offset pagination is acceptable for ordinary administrative pages:

```text
?page=2&per_page=20
```

Cursor pagination is preferable for high-volume continuously changing datasets such as:

```text
messages
notifications
audit_logs
```

when the expected data volume justifies it.

---

# 64. Search

Searchable columns should be indexed appropriately.

Examples:

```text
users.email
users.name

training_listings.title
training_listings.description

companies.name

skills.name
specializations.name
```

For large-scale text search, a dedicated full-text search strategy may be introduced later.

---

# 65. Date and Time

All timestamps should use one consistent server/database timezone strategy.

Recommended:

```text
UTC
```

The application can convert UTC timestamps into the user's local timezone when displaying them.

---

# 66. Date Fields

Business date fields should be distinguished from timestamps.

Examples:

```text
start_date
end_date
application_deadline
```

versus:

```text
created_at
updated_at
```

Do not use timestamps where a date-only value is semantically correct.

---

# 67. Monetary Values

Monetary amounts should not be stored as floating-point values.

Prefer:

```text
DECIMAL
```

with appropriate precision.

Example:

```text
DECIMAL(12,2)
```

The exact precision must follow the business requirements.

---

# 68. Currency

If multiple currencies are supported, store currency explicitly:

```text
currency
```

Do not infer currency from locale.

Example:

```text
amount = 1500.00
currency = EGP
```

---

# 69. Password Storage

Passwords must never be stored as plaintext.

The database should contain only a secure password hash:

```text
password_hash
```

Password hashing must be performed using a modern password hashing algorithm supported by the application runtime.

---

# 70. Secrets

The database must never contain unnecessary application secrets such as:

```text
API private keys
JWT signing secrets
database passwords
payment provider secrets
encryption master keys
```

Secrets belong in secure environment/configuration management.

---

# 71. File Security

File records should include enough metadata to enforce authorization.

Potential metadata:

```text
owner_id
storage_key
original_name
mime_type
size
created_at
```

File downloads must always verify authorization.

---

# 72. Database Backups

Production databases must be backed up regularly.

Recommended backup strategy:

```text
Full backups
+
Incremental/binlog backups where supported
+
Off-site backup storage
```

Backup retention must follow the project's operational requirements.

---

# 73. Restore Testing

A backup is not considered reliable until restore procedures are tested.

The project should periodically verify:

```text
backup creation
backup integrity
restore process
application compatibility
data consistency
```

---

# 74. Migration Strategy

All schema changes must be represented through migration files.

Example:

```text
database/migrations/
001_create_users_table.sql
002_create_students_table.sql
...
023_create_audit_logs_table.sql
```

Do not modify production schema manually without recording the corresponding migration.

---

# 75. Migration Ordering

Migration order should respect dependencies.

Example:

```text
users
  ↓
students / companies
  ↓
training
  ↓
applications
  ↓
sessions
  ↓
certificates
  ↓
appeals
```

Reference tables should be created before tables that reference them.

---

# 76. Seed Data

Reference/master data is inserted through:

```text
database/seeders/
```

Current seeders include:

```text
users_seeder.php
universities_seeder.php
faculties_seeder.php
degrees_seeder.php
specializations_seeder.php
skills_seeder.php
rejection_reasons_seeder.php
```

Seeders should be deterministic where practical.

---

# 77. Seeder Safety

Seeders must not accidentally create duplicate reference records every time they run.

Prefer:

```text
insert if not exists
```

or an equivalent idempotent strategy.

---

# 78. Environment Separation

Development, testing, staging, and production databases must be separate.

```text
development DB
       ≠
test DB
       ≠
production DB
```

Production data must not be copied into development/test environments without appropriate anonymization and authorization.

---

# 79. Test Database

Automated tests should run against an isolated test database.

Tests must not modify:

```text
production database
development database
```

---

# 80. Database Testing

Important database tests include:

```text
foreign-key constraints
unique constraints
status transitions
cascade/restrict behavior
transaction rollback
duplicate prevention
pagination
search filters
```

---

# 81. Data Integrity Examples

### Duplicate Student Skill

Should fail:

```text
student_id = 10
skill_id = 5
```

if the pair already exists.

### Invalid Training Company

Should fail:

```text
training.company_id = 999999
```

if company `999999` does not exist.

### Invalid Application Student

Should fail:

```text
application.student_id = 999999
```

if the student does not exist.

---

# 82. Application Consistency

The database should prevent logically impossible records.

Examples:

```text
Application references nonexistent student
Session references nonexistent application
Certificate references nonexistent session
Appeal references nonexistent certificate
Message references nonexistent conversation
Notification references nonexistent user
```

Foreign keys provide the primary protection.

---

# 83. Historical Data

Historical records should remain queryable where required for:

* Compliance.
* Auditing.
* Certificate verification.
* Application history.
* Payment history.
* Administrative investigations.

Avoid destructive deletion of records that have legal or business significance.

---

# 84. Auditability

Important mutations should create audit records.

Examples:

```text
User role changed
User blocked
Training closed
Application accepted
Application rejected
Certificate revoked
Appeal resolved
Payment status changed
File deleted
```

---

# 85. Database Security

The database account used by the application should have only the permissions it requires.

Avoid using a superuser account for the application.

Production credentials should be stored securely and never committed to source control.

---

# 86. SQL Injection Protection

All application queries must use parameterized statements or the project's database abstraction layer.

Never construct SQL using untrusted string concatenation.

Bad:

```text
SELECT * FROM users WHERE email = '$email'
```

Preferred:

```text
SELECT * FROM users WHERE email = ?
```

with a bound parameter.

---

# 87. Sensitive Data Exposure

Do not expose database internals through API responses.

Avoid returning:

```text
password_hash
internal storage paths
private payment data
database error messages
internal SQL statements
```

---

# 88. Error Handling

Database errors should be logged internally but translated into safe API responses.

Client:

```json
{
    "success": false,
    "message": "An unexpected database error occurred."
}
```

Internal logs may contain the technical exception details.

---

# 89. Concurrency

Critical database operations should account for concurrent requests.

Important examples:

```text
application acceptance
training capacity
payment processing
certificate issuance
role changes
```

Use appropriate:

```text
transactions
row locks
unique constraints
idempotency
```

where required.

---

# 90. Training Capacity

If a training has a maximum capacity, capacity enforcement must be concurrency-safe.

Bad approach:

```text
COUNT accepted applications
↓
if count < capacity
↓
INSERT
```

without transaction/locking protection.

Two simultaneous requests could exceed capacity.

The final implementation should use an atomic or locked approach.

---

# 91. Application Race Conditions

Example:

```text
Student submits application
        │
        ├── Request A
        └── Request B
```

A unique constraint such as:

```text
UNIQUE(student_id, training_id)
```

provides database-level protection against duplicate applications.

---

# 92. Certificate Uniqueness

Certificates should have a unique public verification identifier if certificate verification is supported.

Example:

```text
certificate_number
```

or:

```text
verification_code
```

with:

```text
UNIQUE(...)
```

The identifier must not expose sensitive sequential internal IDs if that creates a security concern.

---

# 93. Public vs Internal IDs

Where appropriate, distinguish between:

```text
internal database ID
```

and:

```text
public resource identifier
```

This can reduce enumeration risks.

The final choice depends on the application's security model.

---

# 94. Database Schema Source of Truth

There must be one authoritative representation of the production schema.

Recommended workflow:

```text
Migration
   ↓
Database
   ↓
Schema export
```

`database/schema/masar.sql` should remain synchronized with the migration set.

---

# 95. Schema Review Checklist

Before deploying a schema change, verify:

```text
[ ] Primary key exists
[ ] Foreign keys exist
[ ] Required fields are NOT NULL
[ ] Unique constraints are correct
[ ] Delete behavior is correct
[ ] Indexes are sufficient
[ ] Status values are valid
[ ] Migration order is correct
[ ] Existing data remains compatible
[ ] Rollback/recovery strategy exists
```

---

# 96. Final Database Dependency Graph

```text
users
 │
 ├── students
 │      │
 │      ├── student_skills ─── skills
 │      │
 │      └── training_applications
 │
 └── companies
        │
        ├── company_specializations ─── specializations
        │
        └── training_listings
                │
                ├── training_specializations ─── specializations
                │
                ├── training_skills ──────────── skills
                │
                └── training_applications
                        │
                        └── training_sessions
                                │
                                └── certificates
                                        │
                                        └── certificate_appeals


users
 │
 ├── conversations
 │       └── messages
 │
 ├── notifications
 │
 ├── files
 │
 ├── payments
 │
 └── audit_logs


universities
    │
    └── faculties
          │
          └── degrees
```

---

# 97. Final Design Principles

The MASAR database should follow these principles:

1. **Referential integrity** — relationships are enforced by foreign keys.
2. **Normalization** — repeated data is avoided.
3. **Controlled denormalization** — duplication is introduced only for justified performance requirements.
4. **Strong constraints** — unique and required fields are enforced at the database level.
5. **Transactional integrity** — related mutations are performed atomically.
6. **Concurrency safety** — critical state changes are protected against race conditions.
7. **Auditability** — important administrative and business operations are traceable.
8. **Security** — passwords, secrets, and sensitive payment data are protected.
9. **Scalability** — indexes and pagination are designed around actual query patterns.
10. **Maintainability** — all schema changes are represented through migrations.
11. **Historical integrity** — important business records are not casually deleted.
12. **Consistency** — migrations, seeders, and `masar.sql` remain synchronized.

The database is the integrity layer of MASAR. Application-level validation should complement database constraints, not replace them.

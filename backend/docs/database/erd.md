# MASAR Database ERD

## Overview

This document describes the main entities and relationships in the MASAR database.

The database is designed around the following major domains:

* Authentication and users.
* Students.
* Companies.
* Academic information.
* Trainings.
* Applications.
* Training sessions.
* Certificates.
* Appeals.
* Messaging.
* Notifications.
* Files.
* Payments.
* Audit logging.

The source of truth for the actual database structure is:

```text
database/migrations/
database/schema/masar.sql
```

---

# 1. High-Level ERD

```text
                         ┌─────────────────┐
                         │     users       │
                         └────────┬────────┘
                                  │
                   ┌──────────────┼──────────────┐
                   │              │              │
                   ▼              ▼              ▼
              ┌─────────┐   ┌───────────┐   ┌────────────┐
              │students │   │ companies │   │   files    │
              └────┬────┘   └─────┬─────┘   └────────────┘
                   │              │
                   │              │
          ┌────────┼───────┐      │
          │        │       │      │
          ▼        ▼       ▼      ▼
     universities skills  degrees company_specializations
          │        │
          ▼        │
      faculties    │
          │        │
          ▼        │
      degrees      │
                   │
                   ▼
             student_skills


                         companies
                             │
                             ▼
                    training_listings
                       │      │
              ┌────────┘      └─────────┐
              ▼                         ▼
training_specializations          training_skills
              │                         │
              └──────────┬──────────────┘
                         │
                         ▼
               training_applications
                         │
                         ▼
                 training_sessions
                         │
                         ▼
                   certificates
                         │
                         ▼
                certificate_appeals


      users
        │
        ├──────────────► conversations
        │                     │
        │                     ▼
        │                  messages
        │                     │
        │                     ▼
        │                   files
        │
        ├──────────────► notifications
        │
        ├──────────────► payments
        │
        └──────────────► audit_logs
```

---

# 2. Users

```text
users
```

is the central authentication and identity table.

### Relationships

```text
users
 │
 ├── 1 : 0..1 ── students
 │
 ├── 1 : 0..1 ── companies
 │
 ├── 1 : N ───── files
 │
 ├── 1 : N ───── notifications
 │
 ├── 1 : N ───── payments
 │
 └── 1 : N ───── audit_logs
```

A user may represent a student, company account, administrator, or another supported role.

---

# 3. Students

```text
students
```

contains student-specific information.

Relationship:

```text
users
  │
  │ 1 : 0..1
  ▼
students
```

A student record belongs to exactly one user.

A user may have at most one student profile.

---

# 4. Companies

```text
companies
```

contains company-specific information.

Relationship:

```text
users
  │
  │ 1 : 0..1
  ▼
companies
```

A company record belongs to exactly one user account when the system uses a one-account-per-company model.

If multiple company employees are later supported, the relationship should be extended through a dedicated company-user membership table rather than duplicating company records.

---

# 5. Universities

```text
universities
```

contains university reference data.

Relationship:

```text
universities
      │
      │ 1 : N
      ▼
faculties
```

A university can contain multiple faculties.

---

# 6. Faculties

```text
faculties
```

belongs to a university.

```text
universities
      │
      │ 1 : N
      ▼
faculties
      │
      │ 1 : N
      ▼
degrees
```

A faculty must reference an existing university.

---

# 7. Degrees

```text
degrees
```

belongs to a faculty.

```text
faculties
     │
     │ 1 : N
     ▼
degrees
```

A degree may be associated with students.

Conceptually:

```text
degrees
   │
   │ 1 : N
   ▼
students
```

The actual foreign-key placement depends on the final migration design.

---

# 8. Specializations

```text
specializations
```

is shared academic/domain reference data.

Specializations can be associated with:

```text
students
companies
training_listings
```

Where a many-to-many relationship is required, junction tables should be used.

---

# 9. Skills

```text
skills
```

contains reusable skills.

Skills are associated with students and trainings through junction tables.

```text
students
   │
   ▼
student_skills
   ▲
   │
skills
```

and:

```text
training_listings
        │
        ▼
 training_skills
        ▲
        │
      skills
```

---

# 10. Student Skills

```text
student_skills
```

is a many-to-many junction table.

```text
students
    │
    │ 1 : N
    ▼
student_skills
    ▲
    │ N : 1
    │
  skills
```

Conceptually:

```text
students N : M skills
```

The junction should prevent duplicate pairs:

```text
(student_id, skill_id)
```

---

# 11. Company Specializations

```text
company_specializations
```

connects companies and specializations.

```text
companies
    │
    │ 1 : N
    ▼
company_specializations
    ▲
    │ N : 1
    │
specializations
```

Conceptually:

```text
companies N : M specializations
```

Duplicate relationships should be prevented by a unique constraint.

---

# 12. Training Listings

```text
training_listings
```

represents training opportunities published by companies.

Main relationship:

```text
companies
    │
    │ 1 : N
    ▼
training_listings
```

A company can publish multiple training listings.

Each training listing belongs to one company.

---

# 13. Training Specializations

```text
training_specializations
```

connects trainings and specializations.

```text
training_listings
        │
        │ 1 : N
        ▼
training_specializations
        ▲
        │ N : 1
        │
specializations
```

Conceptually:

```text
training_listings N : M specializations
```

---

# 14. Training Skills

```text
training_skills
```

connects trainings and skills.

```text
training_listings
        │
        │ 1 : N
        ▼
training_skills
        ▲
        │ N : 1
        │
      skills
```

Conceptually:

```text
training_listings N : M skills
```

---

# 15. Training Applications

```text
training_applications
```

represents a student's application to a training listing.

Main relationships:

```text
students
    │
    │ 1 : N
    ▼
training_applications
    ▲
    │ N : 1
    │
training_listings
```

Conceptually:

```text
student N : M training_listing
```

through:

```text
training_applications
```

A student should not normally be able to create multiple active applications for the same training unless explicitly allowed by the business rules.

Recommended database protection:

```text
UNIQUE(student_id, training_id)
```

when the product requires one application per student per training.

---

# 16. Training Sessions

```text
training_sessions
```

represents an actual training period/session after an application reaches the appropriate state.

Relationship:

```text
training_applications
          │
          │ 1 : N
          ▼
training_sessions
```

Depending on business requirements, an application may have:

```text
0 or 1 session
```

or multiple sessions/history records.

The final cardinality must follow the migration and business rules.

---

# 17. Certificates

```text
certificates
```

represents certificates issued after training completion.

Relationship:

```text
training_sessions
       │
       │ 1 : N
       ▼
certificates
```

A certificate is associated with a student through the training/session chain.

Conceptually:

```text
student
   │
   ▼
application
   │
   ▼
training_session
   │
   ▼
certificate
```

---

# 18. Certificate Appeals

```text
certificate_appeals
```

contains appeals submitted against certificate-related decisions.

Relationship:

```text
certificates
      │
      │ 1 : N
      ▼
certificate_appeals
```

A certificate may have multiple appeal records if the business rules permit repeated appeals.

---

# 19. Conversations

```text
conversations
```

represents messaging conversations.

A conversation may involve multiple users depending on the supported messaging model.

Typical conceptual relationship:

```text
users
   │
   │ N : M
   ▼
conversations
```

If conversations support multiple participants, a dedicated participant/junction table is normally required.

If the current schema models only two participants directly, the relationship should follow that implementation.

---

# 20. Messages

```text
messages
```

belongs to a conversation.

```text
conversations
      │
      │ 1 : N
      ▼
messages
```

A conversation can contain many messages.

Messages are also associated with the sender:

```text
users
   │
   │ 1 : N
   ▼
messages
```

Conceptually:

```text
conversation
     │
     ├── message
     ├── message
     └── message
```

---

# 21. Notifications

```text
notifications
```

belongs to a recipient user.

```text
users
   │
   │ 1 : N
   ▼
notifications
```

Notifications may be generated by events such as:

```text
application accepted
application rejected
training status changed
certificate issued
certificate appeal updated
payment completed
system announcements
```

Notification types should use:

```text
shared/enums/notification_types.php
```

---

# 22. Files

```text
files
```

stores file metadata.

Typical relationship:

```text
users
   │
   │ 1 : N
   ▼
files
```

Other domain objects may reference files depending on the schema.

Examples:

```text
student → CV
message → attachment
certificate → certificate PDF
company → document
```

Physical files are stored under:

```text
storage/
```

while database records are stored in:

```text
files
```

---

# 23. Payments

```text
payments
```

stores payment records.

Typical relationship:

```text
users
   │
   │ 1 : N
   ▼
payments
```

A payment may also reference the business object it belongs to, depending on the final schema.

Possible payment-related resources include:

```text
training
certificate
subscription
other billable resource
```

The exact relationship must follow the implemented payment model.

---

# 24. Audit Logs

```text
audit_logs
```

records important system actions.

Typical relationship:

```text
users
   │
   │ 1 : N
   ▼
audit_logs
```

An audit record may reference the affected entity through:

```text
entity_type
entity_id
```

or explicit foreign keys, depending on the database design.

Example:

```text
user.status_changed
```

could reference:

```text
entity_type = user
entity_id   = 25
```

---

# 25. Complete Relationship Map

```text
users
│
├───────────────┐
│               │
▼               ▼
students      companies
│               │
│               │
├──────┐        ├──────────────┐
│      │        │              │
▼      ▼        ▼              ▼
degrees skills  training       specializations
│      │        │
│      │        ├──────────────┐
│      │        │              │
│      ▼        ▼              ▼
│ student_   training_     training_
│ skills     skills        specializations
│               │
│               ▼
│         training_listings
│               │
│               ▼
└──────► training_applications
                │
                ▼
        training_sessions
                │
                ▼
          certificates
                │
                ▼
       certificate_appeals


users
│
├──────────► notifications
│
├──────────► files
│
├──────────► payments
│
├──────────► audit_logs
│
└──────────► messages
                  │
                  ▼
             conversations
```

---

# 26. Academic Hierarchy

The academic structure is:

```text
University
    │
    └── Faculty
          │
          └── Degree
                │
                └── Student
```

Additional classification:

```text
Student
 ├── Specializations
 └── Skills
```

---

# 27. Training Domain

The training structure is:

```text
Company
   │
   └── Training Listing
          │
          ├── Specializations
          │
          ├── Skills
          │
          └── Applications
                  │
                  └── Training Session
                          │
                          └── Certificate
                                  │
                                  └── Appeals
```

---

# 28. Application Lifecycle

Conceptually:

```text
Student
   │
   ▼
Training Listing
   │
   ▼
Application
   │
   ├── pending
   ├── accepted
   ├── rejected
   └── withdrawn
          │
          ▼
     Training Session
          │
          ▼
      Certificate
```

The actual status values are defined by:

```text
shared/enums/application_statuses.php
shared/enums/training_session_statuses.php
shared/enums/certificate_statuses.php
```

---

# 29. Certificate Lifecycle

```text
Training Session
       │
       ▼
Certificate
       │
       ├── issued
       ├── ...
       └── revoked
              │
              ▼
       Certificate Appeal
```

The exact status transitions must be documented in:

```text
docs/business_rules.md
```

---

# 30. Messaging Domain

```text
User
 │
 ▼
Conversation
 │
 ├── Message
 │     │
 │     └── File Attachment
 │
 └── Participant(s)
```

Messages should never be accessible simply by knowing a message ID.

Authorization must first verify conversation membership.

---

# 31. File Domain

```text
User
 │
 ▼
File
 │
 ├── CV
 ├── Certificate
 ├── Message Attachment
 └── Other Document
```

The filesystem stores the physical object.

The database stores metadata and ownership/access relationships.

---

# 32. Notification Domain

```text
Domain Event
     │
     ▼
Notification
     │
     ▼
User
```

Examples:

```text
Application Accepted
        ↓
Notification
        ↓
Student

Certificate Issued
        ↓
Notification
        ↓
Student

Training Expiring
        ↓
Notification
        ↓
Company
```

---

# 33. Payment Domain

```text
User
 │
 ▼
Payment
 │
 ├── payment_type
 ├── payment_status
 └── related resource
```

Payment types and statuses should be controlled through:

```text
shared/enums/payment_types.php
shared/enums/payment_statuses.php
```

---

# 34. Referential Integrity

Foreign-key relationships should be enforced at the database level wherever practical.

Examples:

```text
students.user_id
faculties.university_id
degrees.faculty_id
student_skills.student_id
student_skills.skill_id
company_specializations.company_id
company_specializations.specialization_id
training_listings.company_id
training_specializations.training_id
training_specializations.specialization_id
training_skills.training_id
training_skills.skill_id
training_applications.student_id
training_applications.training_id
training_sessions.application_id
certificates.session_id
certificate_appeals.certificate_id
messages.conversation_id
messages.sender_id
notifications.user_id
files.owner_id
payments.user_id
audit_logs.user_id
```

The exact column names must match the migrations.

---

# 35. Many-to-Many Relationships

The main many-to-many relationships are:

```text
Students
   N : M
Skills
```

through:

```text
student_skills
```

and:

```text
Companies
   N : M
Specializations
```

through:

```text
company_specializations
```

and:

```text
Training Listings
   N : M
Specializations
```

through:

```text
training_specializations
```

and:

```text
Training Listings
   N : M
Skills
```

through:

```text
training_skills
```

Additional many-to-many relationships may be added if future requirements require them.

---

# 36. Delete Rules

Delete behavior must be explicitly defined.

Recommended principles:

### Users

Do not cascade-delete all historical business records automatically.

Prefer account deactivation or soft deletion where history matters.

### Universities

Do not delete if dependent faculties/students still reference them.

### Skills

Do not delete if active student/training relationships exist unless the relationships are handled safely.

### Trainings

Closing a training should generally be a status operation rather than a physical delete.

### Certificates

Certificates should generally be retained for historical/audit purposes.

### Audit Logs

Audit logs should generally be immutable and retained according to the system retention policy.

---

# 37. Indexing

Foreign keys should normally have indexes.

Important indexes include:

```text
users.email
users.role
users.status

students.user_id

companies.user_id
companies.status

faculties.university_id
degrees.faculty_id

student_skills.student_id
student_skills.skill_id

company_specializations.company_id
company_specializations.specialization_id

training_listings.company_id
training_listings.status

training_specializations.training_id
training_specializations.specialization_id

training_skills.training_id
training_skills.skill_id

training_applications.student_id
training_applications.training_id
training_applications.status

training_sessions.application_id

certificates.session_id
certificates.status

certificate_appeals.certificate_id
certificate_appeals.status

messages.conversation_id
messages.sender_id

notifications.user_id
notifications.created_at

files.owner_id
files.type

payments.user_id
payments.status

audit_logs.user_id
audit_logs.entity_type
audit_logs.entity_id
audit_logs.created_at
```

The final index list must be verified against actual query patterns.

---

# 38. Unique Constraints

Potential unique constraints include:

```text
users.email
```

and junction relationships:

```text
student_skills(student_id, skill_id)

company_specializations(company_id, specialization_id)

training_specializations(training_id, specialization_id)

training_skills(training_id, skill_id)
```

Depending on business rules:

```text
training_applications(student_id, training_id)
```

may also be unique.

---

# 39. Database Source Files

The database is defined through:

```text
database/
├── migrations/
│   ├── 001_create_users_table.sql
│   ├── 002_create_students_table.sql
│   ├── 003_create_companies_table.sql
│   ├── 004_create_universities_table.sql
│   ├── 005_create_faculties_table.sql
│   ├── 006_create_degrees_table.sql
│   ├── 007_create_specializations_table.sql
│   ├── 008_create_skills_table.sql
│   ├── 009_create_student_skills_table.sql
│   ├── 010_create_company_specializations_table.sql
│   ├── 011_create_training_listings_table.sql
│   ├── 012_create_training_specializations_table.sql
│   ├── 013_create_training_skills_table.sql
│   ├── 014_create_training_applications_table.sql
│   ├── 015_create_training_sessions_table.sql
│   ├── 016_create_certificates_table.sql
│   ├── 017_create_certificate_appeals_table.sql
│   ├── 018_create_conversations_table.sql
│   ├── 019_create_messages_table.sql
│   ├── 020_create_notifications_table.sql
│   ├── 021_create_files_table.sql
│   ├── 022_create_payments_table.sql
│   └── 023_create_audit_logs_table.sql
│
└── schema/
    └── masar.sql
```

---

# 40. ERD Design Principle

The database follows a domain-oriented relational model:

```text
Identity
   ↓
Profiles
   ↓
Academic / Company Data
   ↓
Training
   ↓
Applications
   ↓
Sessions
   ↓
Certificates
   ↓
Appeals
```

Cross-cutting services:

```text
Messaging
Notifications
Files
Payments
Audit Logs
```

These services interact with the core domain while maintaining their own data boundaries.

---

# 41. Final Logical Model

```text
                         ┌─────────────┐
                         │    USERS    │
                         └──────┬──────┘
                                │
                ┌───────────────┼────────────────┐
                │               │                │
                ▼               ▼                ▼
           ┌─────────┐     ┌──────────┐     ┌─────────┐
           │ STUDENT │     │ COMPANY  │     │  FILES  │
           └────┬────┘     └────┬─────┘     └─────────┘
                │               │
       ┌────────┼───────┐       │
       │        │       │       ▼
       ▼        ▼       ▼   TRAININGS
    DEGREE   SKILLS  SPECIALIZATIONS
       │        │       │       │
       │        ▼       │       ├─────────────┐
       │  STUDENT_SKILLS│       │             │
       │                │       ▼             ▼
       │                │ TRAINING_SKILLS  TRAINING_SPECIALIZATIONS
       │                │       │             │
       └────────────────┴───────┴──────┬──────┘
                                       ▼
                              APPLICATIONS
                                       │
                                       ▼
                                  SESSIONS
                                       │
                                       ▼
                                 CERTIFICATES
                                       │
                                       ▼
                                    APPEALS


       USERS
         │
         ├────────► CONVERSATIONS ───────► MESSAGES
         │                                   │
         │                                   ▼
         │                                  FILES
         │
         ├────────► NOTIFICATIONS
         │
         ├────────► PAYMENTS
         │
         └────────► AUDIT_LOGS
```

This ERD represents the logical architecture of the MASAR relational database. The concrete schema, column definitions, foreign keys, indexes, constraints, and data types must remain synchronized with the SQL migrations and `database/schema/masar.sql`.

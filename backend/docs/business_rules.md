# MASAR Business Rules

## 1. Overview

This document defines the core business rules governing the MASAR platform.

The rules apply to:

* Users
* Students
* Companies
* Training Listings
* Training Applications
* Training Sessions
* Certificates
* Certificate Appeals
* Payments
* Messaging
* Notifications
* Files
* Administrative operations

Business rules must be enforced at the application/service layer and supported by appropriate database constraints where applicable.

---

# 2. User Rules

## 2.1 User Registration

A user must provide all required registration information.

Required fields must be validated before creating the account.

The email address must be unique.

Passwords must never be stored in plain text.

Passwords must be securely hashed before storage.

---

## 2.2 User Status

A user can only perform normal authenticated operations when the account is in an allowed active state.

Suspended, blocked, or deactivated users must not be allowed to perform operations restricted to active users.

---

## 2.3 User Roles

Supported roles are defined in:

```text
shared/enums/user_roles.php
```

A user's role determines the operations available to that user.

Typical roles include:

```text
Student
Company
Admin
```

A user must not be able to assign a privileged role to themselves.

Role changes must be authorized.

Administrative role changes should be audited.

---

# 3. Authentication Rules

## 3.1 Login

A user must provide valid credentials.

Invalid credentials must not reveal whether the email or password was incorrect.

Repeated failed authentication attempts may be rate-limited.

---

## 3.2 Logout

Logout must invalidate the applicable authentication session/token.

---

## 3.3 Password Security

Passwords must:

* Be hashed using a secure password hashing algorithm.
* Never appear in logs.
* Never be returned through API responses.
* Never be stored in plain text.

---

# 4. Student Rules

## 4.1 Student Profile

A student profile may contain:

* University
* Faculty
* Degree
* Specialization
* Skills
* Personal information
* CV
* Other supported profile information

Required profile information must be completed before operations that require it.

---

## 4.2 Student Skills

A student may have multiple skills.

A skill must exist in the supported skills catalog before it can be attached to a student.

The same skill should not be attached to the same student more than once.

---

## 4.3 CV

A student may upload a CV through the file system.

CV uploads must satisfy:

* Allowed file type.
* Maximum file size.
* Valid upload.
* Secure storage.

A CV must not be directly executable by the web server.

---

# 5. Company Rules

## 5.1 Company Profile

A company must have a valid company record before creating training listings.

Required company information must be completed before publishing a training.

---

## 5.2 Company Status

Company status determines whether the company can perform business operations.

Inactive, suspended, or otherwise restricted companies must not be allowed to create or manage operations that require an active company.

---

## 5.3 Company Ownership

A company may only modify resources that belong to that company.

For example:

```text
Company A
    └── Training 100

Company B
    └── cannot modify Training 100
```

Ownership must be checked server-side.

---

# 6. Training Rules

## 6.1 Training Creation

A training listing must contain all required information before it can be published.

Required information may include:

* Title
* Description
* Company
* Training type
* Training mode
* Start date
* End date
* Application deadline
* Capacity
* Required specializations
* Required skills

---

## 6.2 Training Ownership

Only the owning company or an authorized administrator may modify a training listing.

---

## 6.3 Training Status

Training status is controlled by:

```text
shared/enums/training_statuses.php
```

A training must follow valid state transitions.

Example:

```text
Draft
  ↓
Published
  ↓
Closed
  ↓
Completed
```

Invalid state transitions must be rejected.

---

# 7. Training Publication

A training must satisfy all publication requirements before becoming publicly available.

A training should not be published when required information is missing.

The company must be eligible to publish training.

---

# 8. Training Deadline

A training application deadline determines when students can submit applications.

After the deadline:

```text
New applications → rejected
```

unless a specific administrative rule explicitly permits otherwise.

---

# 9. Training Capacity

If a training has a maximum capacity, the number of accepted participants must not exceed that capacity.

Capacity checks must be performed server-side.

Concurrent acceptance operations must be handled safely to prevent overbooking.

---

# 10. Training Specializations

A training may require one or more specializations.

Each specialization must exist in the specialization catalog.

Duplicate specialization assignments should not be allowed.

---

# 11. Training Skills

A training may require one or more skills.

Each skill must exist in the skills catalog.

Duplicate skill assignments should not be allowed.

---

# 12. Training Types

Training type must be one of the supported values defined in:

```text
shared/enums/training_types.php
```

Unsupported training types must be rejected.

---

# 13. Training Modes

Training mode must be one of the supported values defined in:

```text
shared/enums/training_modes.php
```

Examples may include:

```text
On-site
Remote
Hybrid
```

The actual supported values are controlled by the enum.

---

# 14. Application Rules

## 14.1 Student Eligibility

Only eligible students may apply for a training.

Eligibility may depend on:

* Student status.
* Training status.
* Training deadline.
* Required specialization.
* Required skills.
* Other business requirements.

---

# 15. Duplicate Applications

A student must not submit multiple active applications for the same training.

The system should enforce uniqueness at the database and service levels.

---

# 16. Application Status

Application status is defined in:

```text
shared/enums/application_statuses.php
```

Typical lifecycle:

```text
Pending
   │
   ├──► Accepted
   │
   └──► Rejected
```

Additional states may exist according to the implemented enum.

---

# 17. Application State Transitions

Only valid state transitions are allowed.

For example:

```text
Pending → Accepted
Pending → Rejected
```

An already accepted application should not normally return to pending.

An already rejected application should not normally be accepted unless an authorized administrative workflow explicitly allows it.

---

# 18. Rejection Reasons

When an application is rejected, a rejection reason may be required depending on the business workflow.

Supported rejection reasons are defined in:

```text
shared/enums/rejection_reasons.php
```

The rejection reason must correspond to a valid configured value.

---

# 19. Application Acceptance

Before accepting an application, the system must verify:

1. The application exists.
2. The current application state allows acceptance.
3. The training is still eligible for acceptance.
4. The company/admin has permission to accept it.
5. Capacity has not been exceeded.
6. Any required student conditions remain valid.

The operation should be transactional.

---

# 20. Application Rejection

Before rejecting an application, the system must verify:

1. The application exists.
2. The current state allows rejection.
3. The acting user has permission.
4. Any required rejection reason is valid.

The student should receive the appropriate notification.

---

# 21. Training Session Rules

A training session represents an actual student's participation in a training after the application process reaches the appropriate state.

A session should not be created for an application that has not reached the required accepted state.

---

# 22. Session Ownership

A training session belongs to:

```text
Student
+
Training
+
Accepted Application
```

The system must preserve this relationship.

---

# 23. Session Status

Session status is controlled by:

```text
shared/enums/training_session_statuses.php
```

Only valid transitions are allowed.

Example:

```text
Scheduled
   ↓
In Progress
   ↓
Completed
```

---

# 24. Session Completion

A training session may only be marked completed when the required completion conditions are satisfied.

Completion conditions may include:

* Required training period completed.
* Attendance requirements satisfied.
* Company confirmation.
* Required evaluation completed.

The exact conditions must follow the implemented business workflow.

---

# 25. Certificate Eligibility

A student becomes eligible for a certificate only after satisfying all certificate requirements.

Typical condition:

```text
Training Session
      ↓
Completed
      ↓
Certificate Eligible
```

A certificate must not be issued for an incomplete training session.

---

# 26. Certificate Rules

Certificate status is defined in:

```text
shared/enums/certificate_statuses.php
```

Each certificate must have a unique certificate identifier.

Certificate records must be associated with the correct:

* Student.
* Training.
* Training session.

---

# 27. Certificate Generation

When a certificate is issued:

1. Verify eligibility.
2. Generate unique certificate identifier.
3. Generate certificate document.
4. Store certificate file securely.
5. Create certificate database record.
6. Record audit event.
7. Notify the student.

The operation should avoid creating duplicate certificates for the same completed session unless explicitly supported.

---

# 28. Certificate Verification

Certificate verification must use a unique certificate identifier or supported verification mechanism.

Only certificate information intended for verification should be publicly exposed.

Private student information must not be unnecessarily disclosed.

---

# 29. Certificate Appeals

A student may submit an appeal when the business workflow permits it.

Appeal status is defined in:

```text
shared/enums/appeal_statuses.php
```

Typical lifecycle:

```text
Submitted
    │
    ├──► Under Review
    │
    ├──► Approved
    │
    └──► Rejected
```

---

# 30. Appeal Rules

A student must not submit duplicate active appeals for the same certificate/relevant case.

Only authorized administrators may review and resolve certificate appeals.

Appeal decisions should be audited.

---

# 31. Payment Rules

Payment types are defined in:

```text
shared/enums/payment_types.php
```

Payment statuses are defined in:

```text
shared/enums/payment_statuses.php
```

A payment must have a valid associated business purpose.

---

# 32. Payment Status

A payment should follow valid state transitions.

Example:

```text
Pending
   │
   ├──► Paid
   │
   └──► Failed
```

Refund or cancellation states may be added when supported by the system.

---

# 33. Payment Integrity

Payment state must not be changed solely based on client-provided data.

Payment confirmation must be verified using the trusted payment flow/provider.

Sensitive payment credentials must never be stored unless explicitly required and securely handled.

---

# 34. Messaging Rules

Users may communicate only through conversations they are authorized to access.

A user must be a valid participant in a conversation before:

* Reading messages.
* Sending messages.
* Uploading conversation files.
* Managing conversation-related resources.

---

# 35. Message Ownership

A message belongs to its sender and conversation.

Users must not be able to modify another user's message unless the platform explicitly provides an authorized moderation capability.

---

# 36. Conversation Security

Conversation IDs must not be sufficient to grant access.

The system must verify:

```text
Authenticated User
        +
Conversation Membership
```

before returning conversation data.

---

# 37. Notification Rules

Notification types are defined in:

```text
shared/enums/notification_types.php
```

Notifications may be generated by important business events.

Examples:

```text
Application Submitted
Application Accepted
Application Rejected
Training Expiring
Certificate Issued
Appeal Updated
Payment Updated
New Message
```

---

# 38. Notification Delivery

A notification may have multiple delivery channels.

Possible channels include:

```text
In-app
Email
```

A failure in one delivery channel should not necessarily invalidate the underlying business transaction.

---

# 39. Notification Duplication

The system should avoid sending the same business notification repeatedly for the same event.

Scheduled notification jobs should be idempotent.

---

# 40. File Rules

All uploaded files must pass authorization and validation.

Validation should include:

* File size.
* MIME type.
* Extension.
* Upload integrity.
* Storage destination.

---

# 41. File Ownership

A user may only access files they are authorized to access.

For private files, knowing the file ID must not be sufficient to download the file.

---

# 42. File Storage

User-uploaded files should be stored outside the public web root whenever possible.

Storage locations include:

```text
storage/uploads/cvs/
storage/uploads/temp/
storage/certificates/
```

---

# 43. Temporary Files

Temporary files must have an expiration policy.

Expired temporary files may be removed by:

```text
cron/cleanup_temp_files.php
```

The cleanup job must not delete files still referenced as active resources.

---

# 44. Search Rules

Search results must respect authorization.

A user must not receive resources that they are not permitted to discover.

Search should support pagination.

Search input must be validated and safely parameterized.

---

# 45. Pagination Rules

List endpoints should use a consistent pagination strategy.

The server should enforce reasonable maximum page sizes.

Clients must not be able to request unlimited records in a single request.

---

# 46. Admin Rules

Administrative operations require appropriate administrator privileges.

Admin privileges must be checked server-side.

The client interface must never be considered an authorization mechanism.

---

# 47. Admin Audit Rules

Sensitive administrative actions should create audit records.

Examples:

```text
User suspension
Role change
Company status change
Training moderation
Application override
Certificate decision
Appeal decision
Payment override
```

---

# 48. Ownership Rules

The general ownership rule is:

```text
A user may only modify a resource if:
    the user owns the resource
    OR
    the user has explicit permission
    OR
    the user is an authorized administrator.
```

---

# 49. Resource ID Security

Resource IDs must never bypass authorization.

For example:

```text
GET /trainings/123
```

does not imply that every authenticated user may access training `123`.

The service must determine whether the resource is visible to the requester.

---

# 50. Status Transition Security

Status changes must be performed through controlled business operations.

Clients must not be allowed to arbitrarily submit:

```json
{
  "status": "completed"
}
```

and expect the server to accept it.

The server determines whether the requested transition is valid.

---

# 51. Date Rules

Dates must be validated for logical consistency.

For a training:

```text
start_date < end_date
```

and, where applicable:

```text
application_deadline <= start_date
```

The exact relationship depends on the training workflow.

---

# 52. Past-Date Rules

Operations involving dates in the past must be explicitly validated.

For example, a training that has already ended must not normally be published as a future training.

---

# 53. Timezone Rules

Database timestamps should use a consistent timezone strategy.

Recommended:

```text
Database → UTC
Application → UTC
Presentation → User/Business timezone
```

Date comparisons must use a consistent timezone.

---

# 54. Concurrency Rules

Operations that can modify limited resources must be concurrency-safe.

Examples:

```text
Training capacity
Application acceptance
Payment processing
Certificate issuance
```

The application must prevent two simultaneous requests from violating business constraints.

---

# 55. Idempotency Rules

Operations that may be retried must be designed to avoid duplicate effects.

Examples:

```text
Payment callbacks
Certificate generation
Notifications
Cron jobs
File processing
```

Repeated execution should either:

* Produce the same result.
* Detect that the operation was already completed.

---

# 56. Database Integrity Rules

Important business constraints should also be protected at the database level when possible.

Examples:

```text
Unique email
Unique student-skill relation
Unique training-skill relation
Unique training-specialization relation
Unique application per student/training
Unique certificate identifier
```

Application validation and database constraints should complement each other.

---

# 57. Deletion Rules

Critical business records should generally not be physically deleted when deletion would destroy historical information.

Examples:

```text
Applications
Certificates
Payments
Audit Logs
Messages
```

Depending on requirements, soft deletion or status-based deactivation may be preferable.

---

# 58. Audit Log Rules

Audit logs should preserve important historical actions.

An audit record should contain enough information to identify:

* Actor.
* Action.
* Resource.
* Timestamp.
* Relevant context.

Audit logs should not contain sensitive credentials or secrets.

---

# 59. Data Privacy

The system should expose only information required for the current operation.

Private student information must not be returned unnecessarily.

Private company information must not be exposed to unauthorized users.

Sensitive information should not appear in:

* Logs.
* Error messages.
* Public verification pages.
* API responses without authorization.

---

# 60. API Security Rule

Every protected endpoint must explicitly define:

```text
Authentication requirement
Authorization requirement
Validation rules
Business rules
```

An endpoint must never rely solely on frontend restrictions.

---

# 61. Business Rule Enforcement

Business rules must be enforced server-side.

The frontend may improve user experience, but it cannot be trusted to enforce business restrictions.

---

# 62. Error Handling

When a business rule is violated, the API should return an appropriate error response.

Example:

```text
Student already applied
        ↓
409 Conflict
```

or another appropriate status according to the API contract.

Error responses must not reveal unnecessary internal information.

---

# 63. Notification Trigger Examples

## Application Submitted

```text
Student submits application
        ↓
Application created
        ↓
Company notified
```

## Application Accepted

```text
Company accepts application
        ↓
Application status updated
        ↓
Student notified
        ↓
Training session created if required
```

## Application Rejected

```text
Company rejects application
        ↓
Application status updated
        ↓
Student notified
```

## Certificate Issued

```text
Training completed
        ↓
Certificate generated
        ↓
Student notified
```

---

# 64. Expiration Rules

Expired resources must transition to their appropriate status automatically where the business workflow requires it.

Scheduled jobs are responsible for system-driven expiration.

Example:

```text
Published Training
        │
        ▼
Deadline Passed
        │
        ▼
Closed
```

---

# 65. Trial Period Rules

If the platform contains trial periods, the trial must have:

* Start time/date.
* End time/date.
* Current state.

After expiration, the scheduled expiration job must apply the appropriate transition.

---

# 66. Company Training Visibility

Training visibility depends on training status and applicable publication rules.

A draft training should not be publicly visible.

A closed or expired training should not accept new applications.

---

# 67. Student Application Visibility

Students should be able to see their own application history.

Companies should only see applications associated with their own training listings.

Administrators may have broader access according to their permissions.

---

# 68. Certificate Visibility

A student may access their own certificates.

The issuing company may access certificates related to its training according to authorization rules.

Public verification should expose only the minimum required verification information.

---

# 69. Admin Override

If administrators are allowed to override normal business rules, such actions must:

1. Require explicit admin permission.
2. Validate the requested change.
3. Record an audit log.
4. Preserve the previous state when possible.
5. Avoid silently corrupting historical records.

---

# 70. Business Rule Priority

When multiple rules apply, security and data integrity take priority.

Recommended priority:

```text
Security
   ↓
Authorization
   ↓
Data Integrity
   ↓
Business Rules
   ↓
User Experience
```

A convenient user experience must never bypass a security or integrity rule.

---

# 71. Rule Implementation Principle

Every important rule should ideally exist in three places when appropriate:

```text
Documentation
      +
Application/Service Logic
      +
Database Constraint
```

Not every business rule can be represented as a database constraint, but critical invariants should be protected at the strongest practical layer.

---

# 72. Final Business Rule Principles

MASAR must follow these principles:

1. Users can only perform authorized operations.
2. Companies can only manage their own resources.
3. Students can only submit valid applications.
4. Duplicate applications must be prevented.
5. Training status transitions must be controlled.
6. Training capacity must never be exceeded.
7. Certificates require valid completion.
8. Certificate identifiers must be unique.
9. Appeals require valid eligibility and controlled transitions.
10. Payments must use trusted verification.
11. Conversations require membership authorization.
12. Private files require authorization.
13. Notifications should be idempotent.
14. Scheduled jobs must be idempotent.
15. Critical workflows should use transactions.
16. Sensitive administrative actions must be auditable.
17. Database constraints must protect critical invariants.
18. Client-side validation is never sufficient for security.
19. Historical business records should be preserved where required.
20. Business rules must remain synchronized with implementation and API documentation.

---

# 73. Source of Truth

When implementing or modifying a business workflow, the following order should be considered:

```text
1. Current business requirements
2. This business-rules document
3. Database constraints
4. Service/domain implementation
5. API documentation
6. Frontend behavior
```

If frontend behavior conflicts with server-side business rules, the server-side rules take precedence.

# 🛡️ User Module Security Implementation Guide

## Table of Contents
1. [Architecture Overview](#architecture-overview)
2. [Security Layers](#security-layers)
3. [Data Protection Mechanisms](#data-protection-mechanisms)
4. [Audit Trail System](#audit-trail-system)
5. [Implementation Examples](#implementation-examples)

---

## Architecture Overview

### The Four-Layer Security Model

```
┌─────────────────────────────────────────────────────┐
│ Layer 4: CONTROLLER                                 │
│ ├─ Rate Limiting (4 tiers)                         │
│ ├─ CSRF Validation                                 │
│ ├─ IP Tracking                                     │
│ └─ Request Logging                                 │
├─────────────────────────────────────────────────────┤
│ Layer 3: VALIDATOR                                  │
│ ├─ Input Length Validation                         │
│ ├─ Format Validation (Regex)                       │
│ ├─ Injection Detection                             │
│ ├─ Protected Fields Validation                     │
│ └─ Suspicious Pattern Detection                    │
├─────────────────────────────────────────────────────┤
│ Layer 2: SERVICE                                    │
│ ├─ Business Logic                                  │
│ ├─ Data Backup Creation                            │
│ ├─ Duplicate Checking                              │
│ ├─ Audit Logging                                   │
│ └─ Security Logging                                │
├─────────────────────────────────────────────────────┤
│ Layer 1: REPOSITORY                                │
│ ├─ User Existence Validation                       │
│ ├─ State Validation                                │
│ ├─ Status Transition Validation                    │
│ ├─ Parameterized Queries (No SQL Injection)        │
│ └─ Result Verification                             │
└─────────────────────────────────────────────────────┘
```

---

## Security Layers

### Layer 1: Validator (Input Validation)

**File:** `app/modules/users/validators/user_validator.php`

#### Email Validation
```php
✅ Length: 5-254 characters (RFC 5321)
✅ Format: RFC 5322 compliant regex
✅ No consecutive dots (..)
✅ No suspicious dot placement (^. .*\.@ @\.)
✅ No whitespace
✅ Domain validation
```

#### Full Name Validation
```php
✅ Length: 2-150 characters
✅ No dangerous characters: < > " ' { } ;
✅ No SQL keywords: OR UNION SELECT DROP INSERT UPDATE DELETE EXEC SCRIPT
```

#### Protected Fields
```php
❌ Cannot modify: id, role, status, password, password_hash
❌ Cannot modify: remember_token, verification_token
❌ Cannot modify: created_at, updated_at
```

### Layer 2: Service (Business Logic)

**File:** `app/modules/users/services/user_service.php`

#### Data Backup System
```php
function user_backup_for_audit(array $user): array {
    // Creates a safe snapshot of user data
    // Used in audit logs for:
    // - Tracking what was deleted
    // - Comparing old vs new values
    // - Compliance and regulatory requirements
}
```

#### Email Update Security
```
1. Validate input with validator
2. Normalize email (safe standardization)
3. Check consecutive dots pattern
4. Check suspicious placement patterns
5. Check for duplicates in database
6. Create backup of old data
7. Execute update
8. Log security events
9. Create audit trail
```

#### Account Deletion Process
```
1. Validate user exists
2. Create complete data backup
3. Log deletion attempt
4. Perform soft-delete (status = 'deleted')
5. Create audit log with backup data
6. Log success with domain info
```

### Layer 3: Repository (Data Access)

**File:** `app/modules/users/repositories/user_repository.php`

#### Update Operation
```php
✅ Validate data not empty
✅ Check all values are non-null
✅ Check strings are not empty after trim()
✅ Verify update succeeded
✅ Use parameterized queries (no SQL injection)
```

#### Deactivate Operation
```php
✅ Validate user exists
✅ Prevent re-deletion of deleted users
✅ Maintain soft-delete pattern
```

#### Status Change Operation
```php
✅ Validate user exists
✅ Prevent status transitions on deleted users
✅ Only allow valid status values
```

### Layer 4: Controller (Request Handling)

**File:** `app/modules/users/controllers/user_controller.php`

#### Rate Limiting Tiers
```
Tier 1 - Global:     120 req/min   (prevents distributed attacks)
Tier 2 - IP:         20  req/15min (prevents single attacker concentration)
Tier 3 - User:       15  req/15min (prevents account-specific attacks)
Tier 4 - Sensitive:  10  req/15min (extra protection for critical ops)
```

#### CSRF Protection
```php
✅ Validate cookie vs header match
✅ Use hash_equals() for secure comparison
✅ Regenerate after authentication
```

#### Logging
```
✅ Security logs: access patterns, unauthorized attempts
✅ Audit logs: successful changes, deletions, state transitions
✅ IP tracking: source of all operations
✅ User ID tracking: who performed actions
```

---

## Data Protection Mechanisms

### 1. Injection Prevention

#### SQL Injection
```php
// ❌ VULNERABLE
$sql = "SELECT * FROM users WHERE name = '$name'";

// ✅ PROTECTED (Current Implementation)
$sql = "SELECT * FROM users WHERE name = ?";
$result = db_fetch_one($sql, [$name]);
```

#### NoSQL/Command Injection
```php
// ❌ VULNERABLE
$full_name = $_POST['full_name'];  // Could contain: '); DROP TABLE--

// ✅ PROTECTED
if (preg_match('/or\s+1|union|select|drop|insert|update|delete/i', $full_name)) {
    return error("Suspicious pattern detected");
}
```

#### XSS Prevention (Output Level)
```php
// In response layer (already handled)
// All user data is sanitized before returning
htmlspecialchars($user_data, ENT_QUOTES, 'UTF-8');
```

### 2. Authorization Verification

#### Permission-Based Access
```php
// ❌ VULNERABLE
function user_show($user_id) {
    return user_repository_find_by_id($user_id);  // Anyone can view anyone
}

// ✅ PROTECTED
function user_show($user_id) {
    $current_user = auth_user();
    $is_admin = auth_user_has_role($current_user, ROLE_ADMIN);
    $is_requesting_own = $current_user['id'] === $user_id;
    
    if (!$is_admin && !$is_requesting_own) {
        security_log_event('unauthorized_access', [...]);
        return error('Forbidden');
    }
}
```

### 3. State Protection

#### Deleted User Safety
```php
// ❌ VULNERABLE
function update_status($user_id, $status) {
    return db_execute("UPDATE users SET status = ? WHERE id = ?", [$status, $user_id]);
}

// ✅ PROTECTED
function update_status($user_id, $status) {
    $user = user_repository_find_by_id($user_id);
    if ($user['status'] === 'deleted' && $status !== 'deleted') {
        return false;  // Prevent resurrection
    }
}
```

### 4. Data Integrity

#### Backup Before Modification
```php
$user_backup = user_backup_for_audit($user);
// Store in audit log before any changes
audit_log_user_action('user_updated', 'user', $user_id, $user_backup, [...]);
```

#### Null Value Prevention
```php
// ❌ VULNERABLE
$data = ['email' => null];
db_execute("UPDATE users SET email = ? WHERE id = ?", [$data['email'], $user_id]);

// ✅ PROTECTED
if ($data['email'] === null || trim($data['email']) === '') {
    return false;
}
```

---

## Audit Trail System

### Audit Log Structure

```php
[
    'action' => 'user_deleted',           // What happened
    'entity_type' => 'user',              // Type of entity
    'entity_id' => 123,                   // Which entity
    'old_values' => [...],                // Before backup
    'new_values' => [...],                // After values
    'metadata' => [
        'deleted_at' => '2026-08-12 10:30:00',
        'previous_status' => 'active',
        'new_status' => 'deleted',
    ],
    'timestamp' => '2026-08-12 10:30:00',
    'user_id' => 1,                       // Who did it
    'ip_address' => '192.168.1.1',        // From where
]
```

### Security Log Structure

```php
[
    'event' => 'user_delete_initiated',   // What security event
    'user_id' => 123,                     // Which user
    'ip' => '192.168.1.1',                // From where
    'context' => [
        'email' => 'user@domain.com',
        'role' => 'student',
        'reason' => 'data breach attempt detected',
    ],
    'timestamp' => '2026-08-12 10:30:00',
]
```

### Audit Trail Use Cases

#### Tracking User Deletion
```
1. User requests account deletion
2. Backup created: user_backup_for_audit()
3. Status changed to 'deleted'
4. Audit entry: user_deleted with full backup
5. Security log: user_delete_success
6. Result: Complete historical record
```

#### Tracking Unauthorized Access
```
1. User attempts to view other's profile
2. Permission check fails
3. Security log: user_show_unauthorized_access
4. Audit entry: unauthorized_user_view
5. Result: Suspicious activity detected
```

#### Tracking Email Changes
```
1. Validator: Check input
2. Service: Log attempt
3. Service: Check for duplicates
4. Service: Log domain change
5. Repository: Execute update
6. Service: Create audit trail
7. Result: Complete change history
```

---

## Implementation Examples

### Example 1: Safe User Update

```php
// REQUEST
PUT /api/users/me
{
    "email": "newemail@domain.com",
    "full_name": "Ahmed Hassan"
}

// VALIDATION CHAIN
┌─ Validator Layer
│  ├─ Email: Length 5-254 ✓
│  ├─ Email: No consecutive dots ✓
│  ├─ Email: No suspicious patterns ✓
│  ├─ Email: Valid format ✓
│  ├─ Full name: No SQL keywords ✓
│  ├─ Full name: No dangerous chars ✓
│  └─ No protected fields modified ✓
│
├─ Service Layer
│  ├─ Create backup of old data ✓
│  ├─ Check for duplicate email ✓
│  ├─ Log email change attempt ✓
│  └─ Prepare audit data
│
├─ Repository Layer
│  ├─ Verify email not empty ✓
│  ├─ Execute update query ✓
│  └─ Verify result
│
└─ Controller Layer
   ├─ Check rate limits ✓
   ├─ Validate CSRF token ✓
   ├─ Log security event ✓
   └─ Log audit trail ✓

// RESULT
Response: 200 OK
{
    "data": {
        "user": {
            "id": 1,
            "email": "newemail@domain.com",
            "full_name": "Ahmed Hassan",
            "role": "student",
            "status": "active"
        }
    }
}
```

### Example 2: Blocked Injection Attack

```php
// REQUEST (MALICIOUS)
PUT /api/users/me
{
    "full_name": "Ahmed' OR '1'='1",  // SQL Injection attempt
    "role": "admin"                   // Mass Assignment attempt
}

// VALIDATION CHAIN
┌─ Validator Layer
│  ├─ Email: Not provided (skip) ✓
│  ├─ Full name: Regex check for SQL keywords ✗
│  │   └─ Pattern "OR" detected → ERROR
│  ├─ Protected field check: "role" protected ✗
│  │   └─ "role" cannot be modified → ERROR
│  └─ Return validation errors
│
└─ STOPPED AT VALIDATOR
   Security log: input_validation_failed

// RESULT
Response: 422 Unprocessable Entity
{
    "errors": {
        "full_name": [
            "Full name contains suspicious patterns."
        ],
        "role": [
            "This field is protected and cannot be modified."
        ]
    }
}

// SECURITY LOGGING
Event: input_validation_failed
├─ user_id: 1
├─ ip: 192.168.1.100
├─ error_count: 2
└─ attack_indicators: SQL_injection_attempt, mass_assignment_attempt
```

### Example 3: Rate Limiting

```php
// SCENARIO: User makes 121 requests in 60 seconds
// Rate limit: 120 requests per minute

Request 1-120:    ✓ Allowed
Request 121:      ✗ BLOCKED

// Response to Request 121
Response: 429 Too Many Requests
{
    "error": "Too many requests. Try again later.",
    "retry_after": 45
}

// SECURITY LOGGING
Event: rate_limit_exceeded
├─ endpoint: user_me
├─ tier: global
├─ ip: 192.168.1.1
├─ limit: 120 req/min
└─ reset_in: 45 seconds
```

### Example 4: Account Deletion

```php
// REQUEST
DELETE /api/users/me
{
    "confirm_deletion": "yes-delete-my-account"
}

// PROCESSING
┌─ Controller Layer
│  ├─ CSRF validation ✓
│  ├─ Rate limiting (3 req/15min for deletion) ✓
│  └─ Confirmation token check ✓
│
├─ Service Layer
│  ├─ Load user ✓
│  ├─ Create backup: user_backup_for_audit() ✓
│  ├─ Log deletion attempt ✓
│  ├─ Call repository ✓
│  └─ Log deletion success ✓
│
├─ Repository Layer
│  ├─ Verify user exists ✓
│  ├─ Check not already deleted ✓
│  ├─ Execute soft delete (status = 'deleted') ✓
│  └─ Return success
│
└─ Audit Trail
   ├─ Action: user_deleted
   ├─ Backup: Complete user data snapshot
   ├─ Previous status: active
   ├─ New status: deleted
   └─ IP: 192.168.1.1

// RESULT
Response: 200 OK
{
    "data": {
        "message": "Account deleted successfully."
    }
}

// ALL SESSIONS CLEARED
- JWT tokens invalidated
- Refresh tokens cleared
- Remember cookies deleted
```

---

## Security Headers

### Recommended Additional Headers

```php
// Add to response headers
header('X-Content-Type-Options: nosniff');           // Prevent MIME sniffing
header('X-Frame-Options: DENY');                     // Prevent clickjacking
header('X-XSS-Protection: 1; mode=block');          // XSS protection
header('Strict-Transport-Security: max-age=31536000'); // Force HTTPS
header('Content-Security-Policy: default-src self'); // CSP
header('Referrer-Policy: no-referrer');             // Privacy
```

---

## Compliance & Standards

### Implemented Standards
- ✅ OWASP Top 10 Protection
- ✅ GDPR Compliance (Right to be Forgotten)
- ✅ Data Backup & Recovery
- ✅ Audit Trail Logging
- ✅ Rate Limiting (DDoS Protection)
- ✅ CSRF Protection
- ✅ SQL Injection Prevention
- ✅ XSS Prevention (Output layer)
- ✅ Authorization & Authentication

---

## Monitoring & Alerting

### Events to Monitor

```
🚨 Critical:
- Unauthorized access attempts
- SQL injection attempts
- Mass assignment attempts
- Multiple rate limit violations
- Account deletion requests
- Status changes on system accounts

⚠️ Warning:
- Unusual access patterns
- Multiple failed email validations
- Rapid requests from same IP
- Suspicious character patterns

ℹ️ Info:
- Successful user updates
- Normal access logs
- Rate limit resets
```

---

**Document Version:** 1.0  
**Last Updated:** 2026-08-12  
**Security Level:** 🔐 MAXIMUM

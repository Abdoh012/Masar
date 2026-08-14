# MASAR API — Files

## Overview

The Files API manages file uploads, metadata, access control, downloads, and deletion across the MASAR platform.

It is used by features such as:

* Student CVs.
* Training documents.
* Certificate files.
* Message attachments.
* Company documents.
* Administrative uploads.

Base URL:

```text
/api/files
```

All endpoints require authentication unless explicitly marked as public.

---

# Authentication

Protected endpoints require:

```http
Authorization: Bearer ACCESS_TOKEN
Accept: application/json
```

The authenticated user is determined from the access token.

The client must never be trusted to determine file ownership.

---

# 1. Upload File

Uploads a file to the platform.

### Endpoint

```http
POST /api/files
```

### Content Type

```http
Content-Type: multipart/form-data
```

### Form Fields

```text
file
type
```

Example:

```text
file = resume.pdf
type = cv
```

### Response

```json
{
    "success": true,
    "message": "File uploaded successfully.",
    "data": {
        "id": 250,
        "original_name": "resume.pdf",
        "mime_type": "application/pdf",
        "size": 245760,
        "type": "cv",
        "created_at": "2026-08-07 20:00:00"
    }
}
```

The server must generate the internal storage name.

---

# 2. Get File

Returns metadata for an accessible file.

### Endpoint

```http
GET /api/files/{fileId}
```

### Response

```json
{
    "success": true,
    "data": {
        "id": 250,
        "original_name": "resume.pdf",
        "mime_type": "application/pdf",
        "size": 245760,
        "type": "cv",
        "created_at": "2026-08-07 20:00:00"
    }
}
```

The physical storage path must never be returned to an unauthorized client.

---

# 3. Download File

Downloads an accessible file.

### Endpoint

```http
GET /api/files/{fileId}/download
```

### Response

The server returns the file using the appropriate content type.

Example:

```http
Content-Type: application/pdf
Content-Disposition: attachment; filename="resume.pdf"
```

The download endpoint must verify authorization before reading the physical file.

---

# 4. Delete File

Deletes a file owned by or managed by the authenticated user.

### Endpoint

```http
DELETE /api/files/{fileId}
```

### Response

```json
{
    "success": true,
    "message": "File deleted successfully."
}
```

Deletion should respect references from other records.

A file that is still required by an active certificate, message, or other domain object must not be physically deleted without handling those references.

---

# 5. List My Files

Returns files owned by the authenticated user.

### Endpoint

```http
GET /api/files
```

### Query Parameters

```text
page
per_page
type
```

### Example

```http
GET /api/files?page=1&per_page=20&type=cv
```

### Response

```json
{
    "success": true,
    "data": [
        {
            "id": 250,
            "original_name": "resume.pdf",
            "mime_type": "application/pdf",
            "size": 245760,
            "type": "cv",
            "created_at": "2026-08-07 20:00:00"
        }
    ],
    "meta": {
        "current_page": 1,
        "per_page": 20,
        "total": 1,
        "last_page": 1
    }
}
```

---

# File Types

The application should use a controlled set of logical file types.

Examples:

```text
cv
training_document
certificate
message_attachment
company_document
profile_document
temporary
```

The exact supported values should be centralized rather than scattered throughout controllers.

---

# File Ownership

Every uploaded file must have an owner or an explicitly defined system ownership model.

Recommended relationship:

```text
users
   │
   └── files
```

The API must verify ownership before allowing private operations.

---

# Access Control

A file may be accessible when one of the following conditions is satisfied:

```text
Authenticated user owns the file
OR
Authenticated user has explicit access
OR
File belongs to a resource the user is authorized to access
OR
File is explicitly public
```

Access must be evaluated server-side.

---

# File Storage

Uploaded files should not be stored directly under the public web root.

Recommended structure:

```text
storage/
└── uploads/
    ├── cvs/
    └── temp/
```

Certificates should use:

```text
storage/certificates/
```

The public directory should contain only application entry points and intentionally public assets.

---

# Internal File Name

The uploaded filename must not be used directly as the storage filename.

Bad:

```text
storage/uploads/cvs/resume.pdf
```

Preferred:

```text
storage/uploads/cvs/{generated-id}.pdf
```

or another securely generated storage identifier.

The original filename should be stored as metadata.

---

# Filename Security

The server must protect against:

```text
Path traversal
Directory traversal
Null-byte attacks
Executable file uploads
Double extensions
Unsafe filenames
Unexpected MIME types
```

Examples that must not be trusted:

```text
../../config.php
resume.php.pdf
shell.php
```

The original filename should be treated as display metadata only.

---

# MIME Type Validation

The server must determine the actual file type.

Do not rely solely on:

```text
$_FILES['file']['type']
```

or a client-provided MIME type.

The server should inspect the uploaded file and compare:

```text
Detected MIME type
+
Allowed extension
+
Configured file type
```

---

# Allowed File Types

The allowed extensions and MIME types should be explicitly configured.

Example for CV documents:

```text
pdf
doc
docx
```

Example for certificate files:

```text
pdf
```

The final allowed types should be defined according to the application's requirements.

---

# File Size Limits

Every file category should have a maximum allowed size.

Example:

```text
CV                  → configured limit
Training documents  → configured limit
Message attachments → configured limit
Certificates        → configured limit
```

The server must enforce the limit regardless of client-side validation.

---

# Temporary Files

Temporary uploads should be stored under:

```text
storage/uploads/temp/
```

Temporary files must not remain indefinitely.

The cleanup job:

```text
cron/cleanup_temp_files.php
```

should remove expired temporary files according to the configured retention period.

---

# Upload Flow

Recommended upload flow:

```text
Client
  │
  │ multipart/form-data
  ▼
Upload Controller
  │
  ├── Validate authentication
  ├── Validate file presence
  ├── Validate size
  ├── Validate MIME type
  ├── Validate extension
  ├── Generate storage name
  ├── Move file
  └── Create database record
          │
          ▼
        files
```

The database record should only be considered valid after the physical file has been stored successfully.

---

# Database Consistency

If file storage succeeds but database creation fails, the orphaned physical file should be removed.

If database creation succeeds but the physical move fails, the database transaction must not leave an invalid file record.

Recommended approach:

```text
Validate
   ↓
Store file
   ↓
Create DB record
   ↓
Commit
```

If any step fails:

```text
Rollback
+
Remove temporary/partial file
```

---

# Download Security

The application must not expose arbitrary filesystem paths.

Bad:

```http
GET /storage/uploads/cvs/abc123.pdf
```

Preferred:

```http
GET /api/files/250/download
```

The server resolves file ID `250` to the actual storage path after authorization.

---

# Public Files

If the platform supports public files, public access must be explicit.

A file should not become public simply because it is stored under a web-accessible directory.

Public verification assets should be intentionally exposed through the relevant API or public resource.

---

# File Access in Messaging

Message attachments should follow the same authorization model as messages.

Recommended flow:

```text
Message
   ↓
file_id
   ↓
Files API
   ↓
verify message access
   ↓
download
```

A user who cannot access the conversation must not be able to download its attachment.

---

# File Access in Certificates

Certificate files are associated with certificates.

Recommended flow:

```text
Certificate
   ↓
file_id
   ↓
certificate authorization
   ↓
download
```

Public certificate verification should not automatically make the private certificate PDF publicly downloadable.

---

# CV Files

Student CVs may be used in training applications.

A company should only be able to access a student's CV when the relevant business rules authorize access.

Example:

```text
Student
   ↓
Application
   ↓
Company
   ↓
Authorized CV access
```

The company must not be able to enumerate arbitrary student CV files.

---

# File Metadata

Recommended database fields include:

```text
id
owner_id
original_name
storage_name
storage_path
mime_type
extension
size
type
created_at
updated_at
deleted_at
```

The final schema should match:

```text
database/migrations/021_create_files_table.sql
```

---

# File Relationships

A file may be referenced by other domain objects.

Examples:

```text
Student
   └── CV

Message
   └── Attachment

Certificate
   └── PDF

Training
   └── Document
```

References must be validated before attachment.

---

# File Replacement

If a resource allows replacing an existing file, the operation should:

```text
Upload new file
      ↓
Validate new file
      ↓
Store new file
      ↓
Update DB reference
      ↓
Remove old file when safe
```

The old file should not be removed before the new file is successfully stored.

---

# File Deletion Policy

A file may be:

```text
Active
Deleted logically
Permanently deleted
```

Soft deletion is recommended where auditability or historical references are important.

Certificates and audit-related files should generally have stricter retention rules.

---

# File Enumeration Protection

Users must not be able to discover files by incrementing IDs.

For example:

```text
/api/files/1/download
/api/files/2/download
/api/files/3/download
```

must not provide access merely because the IDs exist.

Every request must perform an authorization check.

---

# Rate Limiting

Upload and download endpoints should be rate limited where appropriate.

Especially:

```text
POST /api/files
GET /api/files/{fileId}/download
```

This helps protect the application against abuse and excessive bandwidth consumption.

---

# Virus / Malware Scanning

If supported by the infrastructure, uploaded files should pass through malware scanning before being made available to other users.

Recommended flow:

```text
Upload
  ↓
Temporary storage
  ↓
Security scan
  ↓
Approved
  ↓
Permanent storage
  ↓
Database record
```

Rejected files should not become accessible through the API.

---

# Error Handling

## 400 Bad Request

```json
{
    "success": false,
    "message": "No file was provided."
}
```

## 401 Unauthorized

```json
{
    "success": false,
    "message": "Unauthenticated."
}
```

## 403 Forbidden

```json
{
    "success": false,
    "message": "You do not have permission to access this file."
}
```

## 404 Not Found

```json
{
    "success": false,
    "message": "File not found."
}
```

## 413 Payload Too Large

```json
{
    "success": false,
    "message": "The uploaded file is too large."
}
```

## 415 Unsupported Media Type

```json
{
    "success": false,
    "message": "This file type is not supported."
}
```

## 422 Validation Error

```json
{
    "success": false,
    "message": "File validation failed.",
    "errors": {
        "file": [
            "The uploaded file type is not allowed."
        ]
    }
}
```

---

# Pagination

File collections support:

```text
page
per_page
```

Example:

```http
GET /api/files?page=1&per_page=20
```

The API should enforce a maximum `per_page`.

---

# Authorization Summary

## Student

May:

```text
Upload own files
View own file metadata
Download own files
Delete own files
```

May access other users' files only where an explicit business rule grants access.

## Company

May:

```text
Upload company files
View own files
Download own files
Delete own files
```

May access student files only when explicitly authorized by the related business process.

## Administrator

May manage files according to administrative permissions.

Administrative file access should be audit logged.

---

# Audit Logging

Important operations may generate audit events:

```text
file.uploaded
file.downloaded
file.deleted
file.replaced
file.accessed
```

Sensitive file access should be logged according to the application's audit policy.

---

# Storage Paths

Current project structure:

```text
storage/
├── uploads/
│   ├── cvs/
│   └── temp/
│
└── certificates/
```

The application should create missing directories safely and ensure correct filesystem permissions.

---

# Related Database

Primary migration:

```text
database/migrations/021_create_files_table.sql
```

Related tables may include:

```text
users
students
companies
messages
certificates
training_listings
```

---

# Related Cron Job

Temporary file cleanup:

```text
cron/cleanup_temp_files.php
```

This job should remove temporary files that have exceeded the configured retention period.

---

# Related Routes

```text
POST   /api/files

GET    /api/files
GET    /api/files/{fileId}
GET    /api/files/{fileId}/download

DELETE /api/files/{fileId}
```

---

# Business Flow

```text
                ┌───────────────┐
                │     Client    │
                └───────┬───────┘
                        │
                        ▼
                 Upload File
                        │
                        ▼
                Validate File
                        │
              ┌─────────┴─────────┐
              │                   │
           Invalid              Valid
              │                   │
              ▼                   ▼
            Reject          Temporary Storage
                                  │
                                  ▼
                            Security Scan
                                  │
                                  ▼
                           Permanent Storage
                                  │
                                  ▼
                             files record
                                  │
                                  ▼
                              Resource
                                  │
                    ┌─────────────┼─────────────┐
                    ▼             ▼             ▼
                  CV          Message      Certificate
```

The Files API is the central boundary for file storage and access. Domain modules should reference file records rather than directly manipulating filesystem paths.
# MASAR API — Search

## Overview

The Search API provides centralized searching and filtering across public and authorized MASAR resources.

It is designed to support searches for:

* Trainings.
* Companies.
* Students.
* Universities.
* Faculties.
* Degrees.
* Specializations.
* Skills.

The Search API must respect authentication, authorization, visibility rules, and resource ownership.

Base URL:

```text
/api/search
```

---

# Authentication

Public search endpoints may be accessible without authentication when the underlying resource is public.

Private search operations require:

```http
Authorization: Bearer ACCESS_TOKEN
Accept: application/json
```

The server determines the authenticated user from the access token.

---

# 1. Global Search

Searches across resources that the current user is allowed to discover.

### Endpoint

```http
GET /api/search
```

### Query Parameters

```text
q
type
page
per_page
```

### Example

```http
GET /api/search?q=php&type=training&page=1&per_page=20
```

### Response

```json
{
    "success": true,
    "data": [
        {
            "type": "training",
            "id": 10,
            "title": "PHP Backend Internship",
            "description": "Backend development training."
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

Only resources that are visible to the authenticated user should appear.

---

# 2. Search Trainings

Searches published training listings.

### Endpoint

```http
GET /api/search/trainings
```

### Query Parameters

```text
q
specialization_id
skill_id
training_type
training_mode
company_id
status
page
per_page
```

### Example

```http
GET /api/search/trainings?q=PHP&training_mode=online&page=1&per_page=20
```

### Response

```json
{
    "success": true,
    "data": [
        {
            "id": 10,
            "title": "PHP Backend Internship",
            "company": {
                "id": 4,
                "name": "Example Company"
            },
            "type": "internship",
            "mode": "online",
            "status": "published"
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

# Training Visibility

Training search results must normally include only listings that are:

```text
published
+
visible
+
not deleted
```

Expired or closed trainings should be excluded from default public search unless explicitly requested and permitted.

---

# 3. Search Companies

Searches companies that are publicly discoverable.

### Endpoint

```http
GET /api/search/companies
```

### Query Parameters

```text
q
specialization_id
status
page
per_page
```

### Example

```http
GET /api/search/companies?q=Software&page=1&per_page=20
```

### Response

```json
{
    "success": true,
    "data": [
        {
            "id": 4,
            "name": "Example Company",
            "status": "active"
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

Only companies satisfying the visibility rules should be returned.

---

# 4. Search Students

Student search is a restricted operation.

### Endpoint

```http
GET /api/search/students
```

### Query Parameters

```text
q
specialization_id
skill_id
university_id
faculty_id
degree_id
page
per_page
```

### Authorization

Student discovery must follow the business rules.

For example, companies may be allowed to discover students for recruitment or training purposes while ordinary users may not.

### Response

```json
{
    "success": true,
    "data": [
        {
            "id": 25,
            "name": "Ahmed Mohamed",
            "university": {
                "id": 1,
                "name": "Example University"
            },
            "skills": [
                {
                    "id": 5,
                    "name": "PHP"
                }
            ]
        }
    ]
}
```

Private student information must not be exposed through search results.

---

# 5. Search Universities

### Endpoint

```http
GET /api/search/universities
```

### Query Parameters

```text
q
page
per_page
```

### Response

```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "Example University"
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

# 6. Search Faculties

### Endpoint

```http
GET /api/search/faculties
```

### Query Parameters

```text
q
university_id
page
per_page
```

### Example

```http
GET /api/search/faculties?q=Engineering&university_id=1
```

---

# 7. Search Degrees

### Endpoint

```http
GET /api/search/degrees
```

### Query Parameters

```text
q
faculty_id
page
per_page
```

---

# 8. Search Specializations

### Endpoint

```http
GET /api/search/specializations
```

### Query Parameters

```text
q
page
per_page
```

### Example

```http
GET /api/search/specializations?q=Software
```

---

# 9. Search Skills

### Endpoint

```http
GET /api/search/skills
```

### Query Parameters

```text
q
page
per_page
```

### Example

```http
GET /api/search/skills?q=PHP
```

### Response

```json
{
    "success": true,
    "data": [
        {
            "id": 5,
            "name": "PHP"
        }
    ]
}
```

---

# Search Filters

Search endpoints should use dedicated filters rather than parsing arbitrary SQL-like expressions from the client.

Example:

```text
q=PHP
training_mode=online
training_type=internship
specialization_id=5
skill_id=10
```

The API converts these parameters into validated database conditions.

---

# Search Query

The `q` parameter represents the user's search text.

Example:

```text
q=backend php
```

The application may search relevant fields such as:

```text
Training title
Training description
Company name
Skill name
Specialization name
```

The exact searchable fields depend on the resource.

---

# Partial Matching

Search should support partial matches where appropriate.

Example:

```text
q=prog
```

may match:

```text
Programming
Programmer
Programming Fundamentals
```

The implementation may use:

* SQL `LIKE`.
* Full-text search.
* Database-specific search features.
* A dedicated search engine.

The API contract should remain independent from the search implementation.

---

# Case Insensitivity

Search should normally be case-insensitive.

These searches should produce equivalent results where supported:

```text
PHP
php
Php
```

---

# Whitespace Normalization

Leading and trailing whitespace should be ignored.

Example:

```text
"   PHP   "
```

should be treated as:

```text
"PHP"
```

---

# Empty Search Query

Endpoints that require a search term should reject an empty `q`.

Example:

```http
GET /api/search/skills?q=
```

Response:

```json
{
    "success": false,
    "message": "Search query is required."
}
```

Endpoints that represent browsing/filtering may allow an empty `q`.

---

# Minimum Search Length

The application may require a minimum query length.

Example:

```text
MIN_SEARCH_LENGTH = 2
```

Then:

```text
q=p
```

may be rejected while:

```text
q=php
```

is accepted.

The actual value should be centralized in configuration.

---

# Maximum Search Length

The API should enforce a maximum query length to prevent abuse.

Example:

```text
MAX_SEARCH_LENGTH
```

Very large search strings should return a validation error.

---

# Pagination

Search results must be paginated.

Supported parameters:

```text
page
per_page
```

Example:

```http
GET /api/search/trainings?q=php&page=2&per_page=20
```

Response:

```json
{
    "meta": {
        "current_page": 2,
        "per_page": 20,
        "total": 73,
        "last_page": 4
    }
}
```

The server must enforce a maximum `per_page`.

---

# Sorting

Search endpoints may support controlled sorting.

Example:

```text
sort
direction
```

Allowed values should be explicitly defined.

For trainings, possible values include:

```text
newest
oldest
deadline
title
```

Example:

```http
GET /api/search/trainings?q=php&sort=newest
```

The client must not be allowed to provide arbitrary SQL column names.

---

# Training Search Example

Request:

```http
GET /api/search/trainings?q=backend&training_mode=online&training_type=internship&page=1&per_page=10
```

Processing:

```text
Search query
     ↓
Validate parameters
     ↓
Apply visibility rules
     ↓
Apply text search
     ↓
Apply filters
     ↓
Apply sorting
     ↓
Paginate
     ↓
Return results
```

---

# Student Search Example

Request:

```http
GET /api/search/students?q=PHP&skill_id=5&university_id=1
```

Processing:

```text
Authenticated company
        ↓
Authorization check
        ↓
Validate filters
        ↓
Search authorized students
        ↓
Remove private fields
        ↓
Paginate
        ↓
Return results
```

---

# Authorization Rules

Search authorization must be resource-specific.

## Guest

May search public resources such as:

```text
Published trainings
Public companies
Universities
Faculties
Degrees
Specializations
Skills
```

Only if those resources are configured as publicly searchable.

## Student

May search:

```text
Public trainings
Public companies
Academic reference data
Skills
Specializations
```

Additional resources depend on the business rules.

## Company

May search:

```text
Public trainings
Companies
Academic reference data
Skills
Specializations
Authorized students
```

Student discovery must be explicitly authorized.

## Administrator

May have broader search access according to administrative permissions.

---

# Privacy Rules

Search must never become a mechanism for enumerating private records.

For example, the following must not be exposed unless authorized:

```text
Private email
Password hashes
Authentication tokens
Private phone numbers
Private files
Internal audit information
Private messages
```

Search responses should use dedicated response objects rather than returning complete database records.

---

# Search Result Shape

Search results should expose only fields required by the client.

Example:

```json
{
    "id": 10,
    "type": "training",
    "title": "PHP Backend Internship"
}
```

Avoid returning:

```text
password
password_hash
internal paths
private tokens
internal database metadata
```

---

# Global Search Result Structure

Global search may return normalized results:

```json
{
    "success": true,
    "data": [
        {
            "type": "training",
            "id": 10,
            "title": "PHP Backend Internship",
            "url": "/trainings/10"
        },
        {
            "type": "company",
            "id": 4,
            "title": "Example Company",
            "url": "/companies/4"
        }
    ]
}
```

The `url` field should only be included if the frontend routing contract requires it.

---

# Search Result Types

Possible global result types:

```text
training
company
student
university
faculty
degree
specialization
skill
```

The final list should match the resources actually exposed by the platform.

---

# Filtering by Status

Status filters must use the project's enums.

Examples:

```text
training_statuses.php
company_statuses.php
user_statuses.php
```

The API should reject unknown status values.

Invalid:

```text
status=random_status
```

Response:

```json
{
    "success": false,
    "message": "Invalid status."
}
```

---

# Training Type and Mode

Training search may use:

```text
training_types.php
training_modes.php
```

Example:

```http
GET /api/search/trainings?training_type=internship&training_mode=hybrid
```

Only enum-defined values are accepted.

---

# Skill Filtering

A training may have multiple skills.

Example:

```text
Training
 ├── PHP
 ├── MySQL
 └── Git
```

Searching by:

```text
skill_id=5
```

should return trainings associated with that skill.

If multiple skill filters are supported, the API must clearly define whether they represent:

```text
ANY skill
```

or:

```text
ALL skills
```

The default behavior should be documented and consistent.

---

# Specialization Filtering

Training and company specialization relationships should use the relevant relationship tables.

For trainings:

```text
training_specializations
```

For companies:

```text
company_specializations
```

Search must use these relationships rather than relying on duplicated text fields.

---

# Search Performance

Search queries must use appropriate indexes.

Likely indexed fields include:

```text
status
created_at
company_id
university_id
faculty_id
degree_id
```

Relationship tables should have indexes on their foreign keys.

Text search strategy should be selected based on expected data size.

---

# N+1 Query Prevention

Search result generation should avoid querying related data one row at a time.

Bad:

```text
Get 20 trainings
   ↓
Query company for each training
   ↓
20 additional queries
```

Preferred:

```text
Get trainings
   +
Load required company data efficiently
```

The exact implementation depends on the database layer.

---

# Search Caching

Reference data searches may be cached where appropriate.

Good candidates:

```text
Universities
Faculties
Degrees
Specializations
Skills
```

Frequently changing resources such as active training listings should use shorter cache lifetimes or no cache depending on requirements.

---

# Rate Limiting

Search endpoints should be rate limited to prevent abuse.

Especially:

```text
GET /api/search
GET /api/search/students
GET /api/search/trainings
```

Rate limits should be applied per authenticated user and/or IP according to the application's security strategy.

---

# Input Security

Search parameters must be treated as untrusted input.

The API must use parameterized database queries.

Never concatenate raw user input into SQL:

```php
$sql = "SELECT * FROM trainings WHERE title LIKE '%" . $_GET['q'] . "%'";
```

Use parameter binding instead.

---

# SQL Injection Protection

All search filters must be parameterized.

Example conceptual query:

```sql
WHERE title LIKE :query
```

with:

```text
:query = "%php%"
```

Sorting fields must come from a server-side allowlist.

---

# Error Handling

## 400 Bad Request

```json
{
    "success": false,
    "message": "Invalid search request."
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
    "message": "You do not have permission to perform this search."
}
```

## 422 Validation Error

```json
{
    "success": false,
    "message": "Validation failed.",
    "errors": {
        "q": [
            "The search query is invalid."
        ]
    }
}
```

---

# Related Database Tables

Depending on the search endpoint, the API may interact with:

```text
users
students
companies
universities
faculties
degrees
specializations
skills
training_listings
training_specializations
training_skills
company_specializations
```

---

# Related Enums

Search filters may depend on:

```text
shared/enums/user_statuses.php
shared/enums/company_statuses.php
shared/enums/training_statuses.php
shared/enums/training_types.php
shared/enums/training_modes.php
```

---

# Related Routes

```text
GET /api/search

GET /api/search/trainings
GET /api/search/companies
GET /api/search/students

GET /api/search/universities
GET /api/search/faculties
GET /api/search/degrees
GET /api/search/specializations
GET /api/search/skills
```

---

# Search Architecture

Recommended architecture:

```text
                    ┌──────────────┐
                    │ Search API   │
                    └──────┬───────┘
                           │
                           ▼
                  Search Validation
                           │
                           ▼
                   Authorization
                           │
                           ▼
                    Search Service
                           │
          ┌────────────────┼────────────────┐
          ▼                ▼                ▼
      Trainings        Companies        Students
          │                │                │
          └────────────────┼────────────────┘
                           ▼
                    Result Formatter
                           │
                           ▼
                         JSON
```

The Search API should remain a thin HTTP layer while search logic is handled by a dedicated service.

---

# Business Flow

```text
User
 │
 │ search request
 ▼
Search Route
 │
 ▼
Controller
 │
 ├── Validate query
 ├── Validate filters
 └── Check authorization
          │
          ▼
    Search Service
          │
          ▼
       Database
          │
          ▼
   Visibility Filters
          │
          ▼
      Pagination
          │
          ▼
    Result Formatter
          │
          ▼
       Response
```

The most important rule is that **search results must always respect the same visibility and authorization rules as direct resource access**.

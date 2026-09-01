# CV Removal Fix Report

## 1. Summary

The `DELETE /api/v1/students/me/cv` endpoint only detached the CV reference and never
actually removed the CV: the physical file stayed on disk and the `files` row stayed in
the database. This report documents the root cause, the fix, the verification performed,
and the remaining limitations.

## 2. Scope

- Backend only (PHP, custom framework). No frontend changes.
- Single endpoint: `DELETE /api/v1/students/me/cv` (`routes/students.php` ->
  `student_controller.php::student_cv_remove` -> `student_profile_service.php::student_profile_remove_cv`).
- Existing API response contract preserved (success/error envelopes, 2xx/4xx/5xx statuses).

## 3. Root Cause

`student_profile_remove_cv()` executed a single SQL statement:

```sql
UPDATE students SET cv_file_id = NULL WHERE id = ?
```

Consequences:

- The physical CV file under `app/storage/uploads/<folder>/` was never deleted.
- The `files` metadata row was never deleted.
- The endpoint reported success even when the student had no CV (no 404 path).

## 4. Files Changed

| File | Change |
|---|---|
| `app/modules/files/services/file_upload_service.php` | Added `file_upload_service_storage_base()` and `file_upload_service_is_safe_storage_path()`; hardened `file_upload_service_delete()` to only unlink paths that pass the containment check. |
| `app/modules/students/services/student_profile_service.php` | Rewrote `student_profile_remove_cv()`; added `require_once` for `core/database/transaction.php`, `modules/files/repositories/file_repository.php`, `modules/files/services/file_upload_service.php`. |
| `phpunit.xml` (new) | PHPUnit configuration (bootstrap = `tests/bootstrap.php`). |
| `tests/bootstrap.php` (new) | Test bootstrap; sets `$GLOBALS['database_config']` so `get_database_connection()` works under PHPUnit's method-scoped include. |
| `tests/CVRemovalTest.php` (new) | 7 tests / 27 assertions covering the fix. |
| `MASAR.json` | Strengthened the "Remove CV" Postman test; moved "Download File" before "Remove CV"; updated description. |
| `docs/api/students.md` | Section 13 (Remove CV) updated to the new contract. |

## 5. Exact Fix

### 5.1 Safe storage-path helper (`file_upload_service.php`)

- `file_upload_service_storage_base()` returns the configured uploads root with a
  trailing separator trimmed (falls back to `file_upload_service_default_config()`).
- `file_upload_service_is_safe_storage_path(string $path): bool` returns `true` only when
  the path is non-empty, absolute, has no `..` segment, and starts with the storage base
  (case-insensitive prefix comparison to tolerate Windows casing).
- `file_upload_service_delete()` now only calls `@unlink` when the stored path passes the
  containment check **and** `is_file()` is true; the `files` row is still deleted via
  `file_repository_delete()`.

### 5.2 Service (`student_profile_service.php`)

`student_profile_remove_cv(int $student_id)` now:

1. Validates the id (`422`), loads the student (`404`), reads `cv_file_id`
   (`404` `No CV found.` when none).
2. Resolves the file record scoped to the student's `user_id`
   (`file_repository_find_for_user`), never from client input.
3. Stale/not-owned reference -> clears the reference only, nothing deleted, still reports
   success (metadata is now consistent).
4. Path safety check: unsafe stored path -> `error_log` and skip the unlink; safe and
   `is_file` -> `@unlink`; unlink failure -> `500` `Unable to delete the CV file.`
5. Missing physical file -> proceeds to clean metadata (graceful).
6. Database cleanup inside a transaction: clear `cv_file_id` first, then delete the
   `files` row (`file_repository_delete`). If either write fails (or an exception is
   thrown), rollback and return `500` `Unable to remove CV metadata.` - never a silent
   success.
7. Success -> `{ 'data' => [ 'message' => 'CV removed successfully.' ] }`.

The DB write order is required by the FK `students.cv_file_id -> files(id) ON DELETE
SET NULL`: deleting the `files` row would auto-null the reference, so clearing the
reference first keeps `UPDATE ... SET cv_file_id = NULL` from reporting a 0-row change.

## 6. Database Behavior

- `files` row for the CV is deleted (scoped by `id` AND `user_id`).
- `students.cv_file_id` is set to `NULL` before the row deletion.
- All writes happen in one transaction (`db_begin_transaction` / `db_commit` /
  `db_rollback`).
- No schema changes; `database/schema/masar.sql` untouched.

## 7. Filesystem Behavior

- Physical file is unlinked only when its stored path is safe (absolute, inside the
  uploads base, no traversal) and the file exists.
- Unsafe stored paths are never deleted (logged instead).
- If the physical file is already gone, metadata cleanup still completes.

## 8. Security Considerations

- The deleted path always comes from the authenticated student's stored `files.path`
  record; the client cannot supply a path.
- Student isolation: the file is resolved with `file_repository_find_for_user($id, $user_id)`;
  a student can never remove another user's CV.
- Path traversal and out-of-tree paths are rejected by `file_upload_service_is_safe_storage_path`
  (empty, relative, `..`, non-absolute, outside-base, and casing-spoof attempts all fail).
- The containment guard also hardened the generic `file_upload_service_delete()` path.

## 9. Tests Performed

- `php -l` clean on all changed PHP files.
- `composer test` (PHPUnit 10.5.64 / PHP 8.4.5): **7 tests, 27 assertions, all pass**.
- Tests cover: happy path (row + physical file removed), no-CV `404`, missing physical
  file (metadata still cleaned), student isolation, unsafe stored path is not unlinked,
  path-traversal rejection, and base-prefix spoof rejection.
- Live HTTP verification (JWT for user 209 / student 101, test file id 23):
  1. Upload CV file -> `200`.
  2. `POST /students/me/cv` (set) -> `200`.
  3. `GET /students/me/cv` -> `200`, `cv_file_id: 23`.
  4. `DELETE /students/me/cv` -> `200` `CV removed successfully.`.
  5. `DELETE /students/me/cv` again -> `404` `No CV found.`.
  6. `GET /students/me/cv` -> `200`, `cv_file_id: null`.
  7. `GET /files` -> empty list.
  8. `GET /files/23` -> `400` `File not found.`.
  9. DB: `files` row for 23 gone; `students.cv_file_id` for 101 null.
  10. Disk: physical file removed (`is_file()` false).

## 10. MASAR.json Changes

- Moved "Download File" before "Remove CV" in the CV/Files folder so the download test
  runs while the file still exists.
- Replaced the "Remove CV" test script with a strengthened version that asserts the
  success envelope + message, then follows up (via `pm.sendRequest`) with:
  - `GET /students/me/cv` -> `cv_file_id` is `null`;
  - `GET /files?limit=100` -> the file id is absent;
  - `GET /files/{id}` -> `to.be.oneOf([400, 404])` (the generic file GET returns 400
    for a missing file);
  - clears the `cv_file_id` / `file_id` collection variables.
- Description updated. File remains valid JSON with CRLF line endings (verified:
  5441 CRLF, 91 requests).

## 11. Remaining Limitations / Notes

- `GET /api/v1/files/{id}` for a missing file returns `400` (existing contract), not
  `404`; the Postman test accepts both.
- If `unlink` succeeds but the DB transaction fails, the physical file is already gone
  (filesystem and DB cannot be rolled back together); the endpoint still returns `500`
  rather than a false success.
- `POST /students/me/cv` (set) does not delete the previously attached CV file; that is
  the existing "replace" behavior and was out of scope for this bug fix.
- No automated test simulates a mid-transaction DB failure; the rollback path is
  exercised by unit tests only via mocking where feasible and by code review.
- `phpunit.xml` and `tests/` were newly created; this also resolves the previously
  documented gap where `composer test` was broken.
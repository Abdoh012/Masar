# MASAR Backend - Errors and Responses

> Endpoint-specific examples: [`docs/api/authentication.md`](docs/api/authentication.md)
> and the rest of [`docs/api/`](docs/api/). The global handlers live in
> `app/core/errors/`.

## Response envelope

Every JSON response uses one envelope, produced by `response_json()`
(`app/core/http/response.php`; `app/core/helpers/response.php` provides
additional helpers):

```json
{
  "success": true,
  "message": "Optional human-readable message",
  "data": {},
  "errors": null
}
```

- `success` is `true` for 2xx statuses and `false` otherwise.
- `message` is `null` when there is nothing to say.
- `data` carries the payload (object, array, or `null`).
- `errors` is `null` unless validation errors are present; when present it is a
  map of `field => [messages]`.

## Helpers

| Helper | Status | Default message |
| --- | --- | --- |
| `response_success` | 200 | - |
| `response_created` | 201 | - |
| `response_no_content` | 204 | - |
| `response_bad_request` | 400 | - |
| `response_unauthorized` | 401 | Authentication required. |
| `response_forbidden` | 403 | You do not have permission to perform this action. |
| `response_not_found` | 404 | Resource not found. |
| `response_method_not_allowed` | 405 | HTTP method not allowed. |
| `response_conflict` | 409 | - |
| `response_validation_error` | 422 | Validation failed. |
| `response_unprocessable` | 422 | - |
| `response_too_many_requests` | 429 | Too many requests. |
| `response_server_error` | 500 | Internal server error. |

## Error codes

Defined in `app/config/constants.php`:

| Constant | Value | Typical status |
| --- | --- | --- |
| `ERROR_VALIDATION` | `VALIDATION_ERROR` | 422 |
| `ERROR_UNAUTHORIZED` | `UNAUTHORIZED` | 401 |
| `ERROR_FORBIDDEN` | `FORBIDDEN` | 403 |
| `ERROR_NOT_FOUND` | `NOT_FOUND` | 404 |
| `ERROR_ALREADY_EXISTS` | `ALREADY_EXISTS` | 409 |
| `ERROR_INVALID_REQUEST` | `INVALID_REQUEST` | 400 |
| `ERROR_SERVER` | `SERVER_ERROR` | 500 |

## Examples

Validation failure (422):

```json
{
  "success": false,
  "message": "Validation failed.",
  "errors": {
    "email": ["The email has already been registered."]
  }
}
```

Unauthorized (401):

```json
{ "success": false, "message": "Authentication required." }
```

Forbidden (403):

```json
{ "success": false, "message": "You do not have permission to perform this action." }
```

Not found (404):

```json
{ "success": false, "message": "Resource not found." }
```

Conflict (409):

```json
{ "success": false, "message": "An account with this email already exists." }
```

Rate limited (429):

```json
{ "success": false, "message": "Too many requests. Please try again in 120 seconds." }
```

## Uncaught errors

- PHP errors are converted to exceptions (`app/core/errors/error_handler.php`)
  and handled by `app/core/errors/exception_handler.php`, which logs the full
  detail server-side (`logger_exception`) and returns the safe envelope.
- **Production responses never include stack traces, file paths, or internal
  detail.** `error_safe_message()` returns `"An internal server error occurred."`
  when the app is in production or `APP_DEBUG` is false.
- A shutdown handler catches fatal errors and returns a 500 envelope when
  headers have not already been sent.
- Technical detail is written to `storage/logs/` only.

## Pagination

List endpoints use a consistent pagination shape driven by `request_get_int`
(`page`, `per_page`; default 20, max 100) and the helpers in
`app/shared/functions/pagination.php`.

# MASAR Backend — Messaging System & Student Academic Status: Complete Technical Report

**Project:** `C:\laragon\www\Masar\backend`
**Scope:** Messaging System (conversations, messages, edit/delete, read state, WebSocket real-time layer) and Student Academic Status (student/graduate profile state, graduation date, graduation certificate), plus their relationships with Education/Skills.
**Type:** Read-only investigation and documentation. No application code, routes, schema, migrations, tests, `MASAR.json`, `openapi.yaml` or frontend files were modified. The only artifact produced by this task is this report.

---

## 1. Executive Summary

Both feature areas are **implemented and covered by passing regression suites** at the time of writing:

| Area | State | Evidence |
|---|---|---|
| Conversations (list/create/access/withdrawal policy) | Implemented | `tests/conversations_list_default_limit_regression.php`, `tests/conversations_list_company_image_regression.php`, `tests/messaging_websocket_regression.php` — ALL PASS |
| Send / edit / hard-delete messages | Implemented | `tests/messaging_edit_delete_regression.php` (includes live WebSocket events) — ALL PASS |
| Cursor pagination of messages | Implemented | `tests/messaging_pagination_regression.php` — ALL PASS |
| Real-time WebSocket delivery (RFC 6455, JWT auth, transactional outbox) | Implemented | `tests/messaging_websocket_regression.php`, `tests/messaging_edit_delete_regression.php` — ALL PASS |
| Message attachments | **Not implemented** (partial/dead code path exists) | `messages` table has no attachment columns; create validator rejects attachment fields; `message_service_attachments()` calls an undefined repository function |
| Message-triggered notifications | **Not implemented** | No notification creation anywhere in the messaging module; `NOTIFICATION_NEW_MESSAGE` constant is unused |
| Mark-as-read / unread-count / search / reply / forward HTTP endpoints | **Implemented in code but not routed** | Controller/service/repository functions exist; `routes/messaging.php` exposes only 6 guards |
| Student/Graduate academic status | Implemented | `tests/student_academic_status_regression.php` — ALL PASS (52 checks) |
| Graduation date + certificate upload/delete | Implemented | Same suite; upload policy enforced (5 MB, pdf/png/jpg/jpeg, content sniffing) |

Important architectural facts confirmed from source:

* A conversation is always tied to **one accepted training application** (`conversations.application_id` UNIQUE); participants are derived from the application, never from client input.
* Withdrawing an application **does not hide the conversation or its history**: both participants keep read access; sending, editing and deleting are refused with 403.
* Editing has **no time limit** and deletion is a **hard delete** ("delete for everyone"), both matching the previously stated requirements.
* Real-time delivery uses a **transactional outbox** (`message_events`) written in the same DB transaction as the message, drained by a separate long-running WebSocket server process.
* Academic status lives on `students.academic_status` (`student` | `graduate`) plus `graduation_date`; it is **explicitly not** a training-eligibility input and is a different vocabulary from `training_applications.applicant_type`.

---

## 2. Investigation Method

Sources inspected:

* Current source: `app/modules/messaging/**`, `app/modules/students/**`, `app/shared/enums/academic_statuses.php`, `routes/messaging.php`, `routes/students.php`, `websocket/server.php`.
* API contracts: `openapi.yaml` (Messaging paths `/conversations`, `/conversations/{id}/messages`, `/conversations/{id}/messages/{message_id}`; Students `/students/me/academic-status` and `/certificate`; `x-websocket` extension) and `MASAR.json` (folders `Messaging` = 6 requests, `Students` → subfolder `Academic Status` = 3 requests).
* Database: `database/schema/masar.sql`, `database/migrations/035…041`, and a **live `information_schema`/index inspection** of `conversations`, `messages`, `message_events`, `students`.
* Tests: 7 suites executed directly (results in §22).
* Git history: `git log` over the relevant paths (coarse bulk commits — see §21).

Every statement below is grounded in the inspected code. Where a previously discussed requirement is **not** satisfied by the implementation, this is called out explicitly.

---

## 3. Messaging Architecture

### 3.1 Layered flow

```
HTTP request
  → routes/messaging.php            (path/method guards, per-route middleware)
  → controllers/*.php               (parse input, auth identity, format response)
  → services/*.php                  (business rules, authorization, transactions orchestration)
  → repositories/*.php              (all SQL through app/core/database/query.php helpers)
  → MySQL
WebSocket branch
  → websocket/server.php (CLI)      (JWT handshake, subscribe/unsubscribe/ping)
  → websocket/websocket_server.php  (event loop, outbox drain, ping/pong, broadcast)
  → websocket/websocket_hub.php     (in-memory connection registry + subscriptions)
  → websocket/websocket_protocol.php(RFC 6455 frame codec, handshake response)
  → message_event_repository.php    (outbox: pending rows, mark dispatched)
```

### 3.2 Route surface (actual, from `routes/messaging.php`)

Only these six guards exist:

| # | Method | Path | Controller |
|---|---|---|---|
| 1 | GET | `/api/v1/conversations` | `conversation_controller_index()` |
| 2 | POST | `/api/v1/conversations` | `conversation_controller_create()` |
| 3 | GET | `/api/v1/conversations/{id}/messages` | `message_controller_index($conversation_id)` |
| 4 | POST | `/api/v1/conversations/{id}/messages` | `message_controller_create($conversation_id)` |
| 5 | PATCH | `/api/v1/conversations/{id}/messages/{message_id}` | `message_controller_edit($conversation_id, $message_id)` |
| 6 | DELETE | `/api/v1/conversations/{id}/messages/{message_id}` | `message_controller_delete_message($conversation_id, $message_id)` |

Anything else falls through to `response_not_found('Messaging endpoint not found.')`.

> Consequence: controller actions such as `conversation_controller_mark_read()`, `conversation_controller_unread_count()`, `conversation_controller_search()`, `message_controller_mark_read()`, `message_controller_mark_conversation_read()`, `message_controller_reply()`, `message_controller_forward()`, `message_controller_attachments()`, `message_controller_show()`, legacy `message_controller_update()/delete()`, and the two `*_dispatch()` routers are **implemented but unreachable over HTTP**.

### 3.3 File responsibilities

| File | Responsibility |
|---|---|
| `routes/messaging.php` | Boots config/constants/http/auth, requires both controllers, dispatches the 6 guards |
| `controllers/conversation_controller.php` (964 lines) | Auth identity resolution, request parsing, response formatting; conversation index/create/update/rename/archive/restore/delete/participants/mark-read/unread/search + dispatcher |
| `controllers/message_controller.php` (1148 lines) | Same for messages: index/show/create/edit/delete_message/update/delete/mark_read/mark_conversation_read/unread/search/reply/forward/attachments + dispatcher; rate-limit hook on send |
| `services/conversation_service.php` (963 lines) | Access authorization (`conversation_service_access`), withdrawal policy (`conversation_service_messaging_blocked_reason`), list/create/update/rename/archive/restore/delete/participants/mark_read/unread/search |
| `services/message_service.php` (991 lines) | Send/edit/delete/read/search/reply/forward/attachments business rules; reuses `conversation_service_access` |
| `repositories/conversation_repository.php` (1375 lines) | All conversation SQL: list with joins and correlated subqueries, company meta, create (idempotent), archive/restore/delete, participants, mark-read, unread count, search |
| `repositories/message_repository.php` (1134 lines) | All message SQL: cursor-paginated list, find (with/without user scope), transactional create/edit/hard-delete, mark-read, unread count, search |
| `repositories/message_event_repository.php` (129 lines) | Outbox writes inside caller transactions; pending fetch; mark dispatched; discard stale backlog; latest-message baseline |
| `validators/conversation_validator.php` (118 lines) | Query-string filter validation for the conversation list (limit/offset/application_id/keyword) |
| `validators/message_validator.php` (1249 lines) | Body/type/id/pagination validation; `MESSAGE_MAX_LENGTH = 5000`, page defaults 12 / max 20; unsupported-field rejection |
| `websocket/websocket_server.php` (764 lines) | Config, JWT authentication, handshake, client frames, event loop, outbox relay, ping/pong |
| `websocket/websocket_hub.php` (285 lines) | In-memory connections, subscriptions with baselines, targeted broadcast, outbound buffer/flush |
| `websocket/websocket_protocol.php` (254 lines) | HTTP upgrade parsing, handshake response, frame encode/decode |
| `websocket/server.php` (33 lines) | CLI entry point (`php websocket/server.php`) |

### 3.4 Authentication & authorization

* **HTTP:** `middleware_student()` / `middleware_company()` are not used in `routes/messaging.php`; instead each controller resolves the authenticated user via `auth_id()` → `current_user_id()` → `$_SESSION['user_id']` and returns 401 when absent. (Route-level guards for student/company live in their own domain route files; messaging endpoints accept either role because both sides participate.)
* **Conversation access** (`conversation_service_access`, lines 153–233):
  1. conversation must exist → 404;
  2. user must be a participant (student side or company side) → 403;
  3. if linked to an application, the user must also be that application's student/company → 403.
* **Per-action ownership:** send forces `sender_id = authenticated user` and rejects mismatched `sender_user_id/sender_id/user_id` body values; edit/delete re-read `sender_user_id` from the stored row and return 403 "You can only edit/delete your own messages."
* **WebSocket:** handshake authenticates with the same JWT access tokens (`Authorization: Bearer …` header or `?token=` query for browsers); signature, expiry, token type and active-user status are validated against the same config/DB. Subscription re-runs `conversation_service_access()` on every `subscribe` message. A token is never logged.

---

## 4. Conversation Management

### 4.1 Creation — `POST /api/v1/conversations`

Request body: `{ "application_id": <int> }` (JSON).

Rules (service `conversation_service_create`, lines 411–651):

1. `application_id` required and positive → otherwise 422 `errors.application_id`.
2. Unsupported fields `title`, `name`, `participant_id`, `participant_ids`, `participants` are **rejected** with 422 ("… is not supported by the conversations API.") rather than silently dropped. Group conversations are not supported.
3. Application must exist → 404.
4. Application must be **`accepted`** → 422 "Chat is only available for accepted training applications."
5. Authenticated user must be the application's student or company → 403.
6. Idempotent: `conversations.application_id` is UNIQUE; an existing conversation returns **200** `"Conversation already exists."`; a new one returns **201** `"Conversation created successfully."`. A concurrent insert race (PDO error 1062) resolves to the winning row.

The student and company are derived from `training_applications` → `training_listings.company_id`; the client never supplies them.

Repository `conversation_repository_create` (lines 966–1218) resolves `student_id`/`company_id` from the application, handles the duplicate-key race, and returns `{conversation, created}`.

### 4.2 Listing — `GET /api/v1/conversations`

Query-string filters (validated by `conversation_validator_list_filters`; invalid → 400 with field errors):

* `limit` default **20**, range 1–100;
* `offset` default 0, ≥ 0;
* `application_id` optional positive int — a filter for an application the user is not part of returns an **empty page**, not an error;
* `keyword` optional LIKE over `students.full_name`, `companies.legal_name`, `training_listings.title`.

Repository list SQL (lines 308–510) joins students, companies, training applications and listings, and computes per row:

* `last_message_id` (correlated subquery, newest by `created_at DESC, id DESC`);
* `unread_count` (correlated `COUNT(*)` of messages from others with `read_at IS NULL`).

`conversation_repository_decorate_latest_messages()` then performs **one batched lookup** for the last-message bodies (no per-row queries, no full history load). Ordering: `COALESCE(updated_at, created_at) DESC, id DESC`.

Response shape:

```json
{
  "success": true,
  "message": "Conversations retrieved successfully.",
  "data": {
    "items": [
      {
        "id": 262, "student_id": 1454, "company_id": 100161, "application_id": 2009,
        "student_name": "Omar Khaled El-Shazly",
        "company_name": "Green Retail",
        "company_image": "companies/20260821_a1b2c3d4.png",
        "training_title": "Backend Development Internship",
        "training_starts_at": "2026-09-01 09:00:00",
        "training_ends_at": "2026-11-30 17:00:00",
        "application_status": "accepted",
        "last_message": { "id": 1289, "body": "Hello…", "sender_user_id": 100588, "created_at": "2026-11-05 12:08:04" },
        "unread_count": 0
      }
    ],
    "pagination": { "total": 12, "limit": 20, "offset": 0 }
  },
  "errors": null
}
```

`company_image` is the company's relative storage path from `companies.company_logo`, normalized empty → `null` (verified by `tests/conversations_list_company_image_regression.php`).

### 4.3 Conversation header (WhatsApp-style)

`GET /conversations/{id}/messages` additionally returns top-level metadata:

```json
"conversation": { "id": 262, "company": { "id": 100161, "name": "Green Retail", "image": "companies/….png" } }
```

Sourced by `conversation_repository_company_meta()` (single scoped query; `null` company when none). Never repeated inside message objects (verified by `tests/messaging_conversation_meta_regression.php`).

### 4.4 Withdrawal behavior (exact rules)

`conversation_service_messaging_blocked_reason()` (lines 246–284) reads the application status **fresh from the database on every call** (never cached) and returns:

> `"Messaging is disabled because the training application was withdrawn."`

when `training_applications.status = 'withdrawn'`.

Applied to:

| Action | After withdrawal |
|---|---|
| List conversations / read messages | **Allowed** (full history readable by both participants) |
| Send message | 403 + `errors.application_id` |
| Edit message | 403 + `errors.application_id` |
| Delete message | 403 + `errors.application_id` |

The conversation row and message history are **not deleted, hidden or archived** by a withdrawal.

---

## 5. Sending Messages

`POST /api/v1/conversations/{id}/messages`

Request body (JSON): `body` is canonical; `message` and `content` are accepted aliases for backward compatibility.

Validation (`message_validator_create_payload`, lines 1307–1391; `message_validator_body_value`, lines 1231–1304):

* must be a string/number; arrays/objects rejected ("Message body must be a string.");
* trimmed, non-empty;
* valid UTF-8;
* maximum **5000 characters** (`MESSAGE_MAX_LENGTH`);
* unsupported fields `message_type`, `reply_to_id`, `attachment_ids`, `attachments`, `file_id` are rejected with 422 ("… is not supported by the messaging API.").

Controller guarantees (lines 394–577):

* URL conversation id is authoritative; a body `conversation_id` must match it;
* sender is always the authenticated user — mismatched `sender_user_id`/`sender_id`/`user_id` values are a 422;
* optional rate limiting via `security_check_rate_limit_tier('user', 'message_send', …, 60, 60)` (no-op locally when `RATE_LIMIT_ENABLED=false`);
* failure mapping: 404 not found, 403 forbidden, 422 validation, else 500.

Persistence (`message_repository_create`, lines 588–775) runs in **one transaction**:

1. `INSERT INTO messages (conversation_id, sender_user_id, body, is_read=0, created_at)`;
2. `UPDATE conversations SET updated_at = CURRENT_TIMESTAMP`;
3. `message_event_repository_create(['message_id', 'conversation_id', 'sender_user_id', 'body', 'created_at'])` → outbox row (default `event_type='message.created'`).

Any failure rolls back and is logged (`logger_database_error`) and rethrown; the service maps it to a safe 500.

Success: **201** `"Message sent successfully."` with the full message (`m.*` plus `sender_id` alias).

Real-time: the outbox row is later drained by the WebSocket server and broadcast as `message.created` (see §9).

---

## 6. Message Editing

`PATCH /api/v1/conversations/{id}/messages/{message_id}`

Requirement under test: *users can edit their own messages at any time; no time restriction.* **Verified: satisfied.**

Service `message_service_edit` (lines 426–585):

* requires valid ids and participant access (`conversation_service_access`);
* applies the withdrawal block (403 after withdrawal);
* loads the message with message id **and** URL conversation id (`message_repository_find_for_user`), so a message from another conversation is 404;
* ownership re-checked from the row: only the sender may edit → 403 `"You can only edit your own messages."`;
* body validated with the same rules as creation (5000 chars, UTF-8, non-empty, aliases accepted);
* **no time limit** anywhere in the code path.

Repository `message_repository_edit` (lines 894–995), in one transaction:

```sql
UPDATE messages
   SET body = :body, is_edited = 1, updated_at = NOW()
 WHERE id = :message_id AND conversation_id = :conversation_id AND sender_user_id = :sender_id
```

Exactly one row must be affected; then an outbox row `event_type='message.updated'` is written with the new body. `id` and original `created_at` are never modified.

Real-time: subscribers receive `message.updated` **after commit**, carrying `{event, conversation_id, message: {id, sender_user_id, body, is_edited, created_at, updated_at}}`.

Verified end-to-end by `tests/messaging_edit_delete_regression.php`: HTTP edit 200, `is_edited=1`, both participants receive `message.updated`, id preserved.

---

## 7. Message Deletion

`DELETE /api/v1/conversations/{id}/messages/{message_id}`

Requirements under test: *delete for everyone; hard deletion; no time restriction unless specified.* **Verified: satisfied.**

Service `message_service_delete_message` (lines 594–729): participant access, withdrawal block, message-in-conversation check, sender ownership — identical pattern to editing. No time limit.

Repository `message_repository_delete_for_conversation` (lines 1009–1097), in one transaction:

```sql
DELETE FROM messages
 WHERE id = :message_id AND conversation_id = :conversation_id AND sender_user_id = :sender_id
```

* **Hard delete** — the row is physically removed; there is no soft-delete flag, placeholder body or archive. (Migration 041 added `is_edited`/`updated_at`; nothing adds `deleted_at`.)
* Outbox event `message.deleted` is written with an **empty body** so the deleted content is never broadcast.
* Already-deleted/nonexistent → 404; not owner → 403; non-participant → 403.

Effects confirmed by tests: the message disappears from retrieval, no longer contributes to `unread_count`, and the conversation's `last_message` is recalculated from remaining messages (`null` when none remain). Subscribers receive:

```json
{ "event": "message.deleted", "conversation_id": 262, "message_id": 1234, "deleted_by": 100588 }
```

No body, no private data.

> Note: legacy functions `message_controller_delete()` / `message_service_delete()` / `message_repository_delete()` still exist (sender-scoped hard delete without conversation scoping and without outbox event) but are **not routed**; the routed endpoint uses the scoped, outbox-emitting variant above.

---

## 8. Read and Unread Functionality

Data model (no per-recipient read table):

* `messages.is_read` tinyint(1) default 0;
* `messages.read_at` datetime null.

Behavior:

* A message is marked read **globally** for the conversation, excluding the sender's own messages (`sender_user_id <> :user_id`).
* `message_repository_mark_read()` — marks one message read (must be a participant-readable message).
* `message_repository_mark_conversation_read()` / `conversation_repository_mark_read()` — mark all currently-unread incoming messages in a conversation (`read_at IS NULL`).
* `unread_count` per conversation = `COUNT(*)` of messages where `sender_user_id <> viewer` and `read_at IS NULL` (used in the conversations list).
* Global unread conversation count = `COUNT(DISTINCT c.id)` of conversations having ≥1 unread incoming message.

Endpoints: `message_controller_mark_read`, `message_controller_mark_conversation_read`, `conversation_controller_mark_read`, `conversation_controller_unread_count`, `message_controller_unread_count` and their service/repository implementations all exist — **but none is exposed by `routes/messaging.php`**. There is consequently no HTTP way to mark read or fetch an unread total; the unread counts remain available inside the conversations list payload.

WebSocket: no read-receipt event is emitted; read state changes are not broadcast.

---

## 9. Real-Time WebSocket Implementation

This is a **real RFC 6455 WebSocket server**, not polling.

### 9.1 Process and configuration

* Entry point: `php websocket/server.php` → `ws_server_run(ws_server_config())`.
* Env-driven config (`WS_*`): host `127.0.0.1`, port `8081`, max connections `100`, ping interval `30`s, outbox poll `500`ms, outbox batch `50`.
* Single-process PHP core streams; no `ext-sockets`/`pcntl`.

### 9.2 Handshake and authentication

1. Read the HTTP upgrade request (`ws_read_http_request`), require `Upgrade: websocket`, `Connection: Upgrade`, `Sec-WebSocket-Version: 13`, valid base64 16-byte key; else HTTP 400 and close.
2. Parse the query string only (`/?token=…`), extract token from `Authorization: Bearer …` or `?token=`.
3. `ws_server_authenticate`: optional IP rate limit (`ws_connect`, 30/min), `jwt_validate_access_token()`, then `auth_find_user_by_id()` + `auth_user_is_active()`. Failures → HTTP 401 with `WWW-Authenticate: Bearer`.
4. Connection limit → HTTP 503 `server_full`.
5. Success → `101` handshake response, non-blocking stream, registered in the hub.

Tokens are never logged.

### 9.3 Client messages and acks

| Client `type` | Effect | Ack / error |
|---|---|---|
| `{"type":"ping"}` | – | `{"type":"pong","data":{}}` |
| `{"type":"subscribe","conversation_id":N}` | Re-checks `conversation_service_access()`; captures `baseline_message_id = MAX(messages.id)` for the conversation | `{"type":"subscribed","data":{"conversation_id":N,"baseline_message_id":B}}`; errors `validation_error`, `unauthorized`, `forbidden`, `not_found` |
| `{"type":"unsubscribe","conversation_id":N}` | Drops subscription | `{"type":"unsubscribed","data":{"conversation_id":N}}` |
| anything else | – | `{"type":"error","error":"unknown_event",...}` |

### 9.4 Server events (payloads straight from `ws_server_build_event_payload`, lines 732–794)

**message.created** (default branch):

```json
{ "type": "message.created",
  "data": { "event_id": 42, "conversation_id": 225, "message_id": 1234,
            "sender_user_id": 100532, "body": "Hello!", "created_at": "2026-11-20 10:15:00" } }
```

**message.updated** (row re-read from DB; skipped if the message no longer exists):

```json
{ "event": "message.updated", "conversation_id": 262,
  "message": { "id": 1234, "sender_user_id": 100532, "body": "Edited body",
               "is_edited": true, "created_at": "…", "updated_at": "…" } }
```

**message.deleted** (deliberately no body):

```json
{ "event": "message.deleted", "conversation_id": 262, "message_id": 1234, "deleted_by": 100532 }
```

Error/ack frames: `{"type":"error","error":<code>,"message":<text>}`, `{"type":<ack>,"data":{…}}`.

Delivery rule (`ws_hub_broadcast_event`): only connections subscribed to that conversation **and** whose baseline is older than the event's message id receive it — history is never replayed to a fresh subscriber, and a message is delivered exactly once.

### 9.5 Reliability

* Outbox row inserted in the **same transaction** as the message → no event before commit, none for failed writes.
* Pending rows drained every `WS_OUTBOX_INTERVAL_MS` in batches; marked `dispatched_at` afterwards.
* At startup, stale pending rows are **discarded** (`message_event_repository_discard_pending`) — downtime backlog is intentionally not replayed; clients recover via `GET /conversations/{id}/messages?after_id=<last_seen_id>`.
* Server ping every `WS_PING_INTERVAL`; missed pongs (> 3 intervals) close stale connections; protocol errors/binary frames close with 1003/unsupported_frame.
* If the outbox drain throws (e.g. stale PDO handle in the long-lived process), the server exits so a supervisor restarts it; messages stay in MySQL.

Restrictions: chat cannot be sent over the socket; all business rules (including the withdrawal send block) are enforced by HTTP only.

Documentation: `openapi.yaml` `x-websocket` extension (entry point, connection URL `ws://127.0.0.1:8081/?token=ACCESS_TOKEN`, handshake errors, config, client messages, server events, reliability, restrictions) and `docs/WEBSOCKET.md`.

Verification: `tests/messaging_websocket_regression.php` covers bad JWT 401, subscribe authorization, baseline suppression, single delivery, no leakage of tokens, withdrawal block producing no event, downtime persistence + `after_id` recovery — **ALL PASS**.

---

## 10. Attachments and Notifications

### 10.1 Attachments — **not implemented**

* `messages` has no attachment columns; `message_events` neither.
* `message_validator_create_payload` rejects `attachment_ids`, `attachments`, `file_id`, `message_type`, `reply_to_id` with 422.
* Dead/partial code: `message_validator_attachment()` (line 1086) is defined but unused; `message_controller_attachments()` and `message_service_attachments()` exist but are **not routed**, and `message_service_attachments()` calls `message_repository_attachments()`, which is **not defined anywhere** in the codebase — invoking it would fatal.
* No file-storage or deletion logic exists for messages (no message-file relationship).

### 10.2 Notifications — **not implemented for messaging**

* Sending a message creates **no notification row**. `notification_service_create()` is called only from the certificates module (`certificate_service.php`, `certificate_appeal_service.php`).
* `NOTIFICATION_NEW_MESSAGE = 'new_message'` is defined in `constants.php` and `app/config/mail.php` carries a `'new_message' => false` preference, but no production code path uses the constant or creates that notification type.
* Real-time delivery is exclusively the WebSocket outbox path (§9); HTTP clients poll the message endpoints.

---

## 11. Complete Messaging API Reference

Base: `http://localhost/Masar/backend/api/v1` (Laragon local; base URL configurable). All endpoints require a Bearer access token unless noted.

### 11.1 `GET /api/v1/conversations`
* **Purpose:** list the authenticated user's conversations.
* **Auth:** any authenticated participant (student or company).
* **Query params:** `limit` (1–100, default 20), `offset` (≥0, default 0), `application_id` (positive int), `keyword` (string).
* **Errors:** 400 invalid query params; 401 unauthenticated; 500.
* **Response:** `data.items[]` (conversation row incl. `student_name`, `company_name`, `company_image`, `training_title`, `training_starts_at/ends_at`, `application_status`, `last_message`, `unread_count`) + `data.pagination {total, limit, offset}`.
* **Controller/service:** `conversation_controller_index()` → `conversation_service_list()`.

### 11.2 `POST /api/v1/conversations`
* **Purpose:** start (or return) the conversation for an accepted application.
* **Body:** `{ "application_id": <int> }`.
* **Errors:** 401; 403 not the application's student/company; 404 application missing; 422 invalid id / non-accepted application / unsupported fields; 500.
* **Response:** 201 created / 200 already exists, `data` = conversation row.
* **Controller/service:** `conversation_controller_create()` → `conversation_service_create()`.

### 11.3 `GET /api/v1/conversations/{id}/messages`
* **Purpose:** cursor-paginated message history.
* **Auth:** conversation participant (student or company).
* **Query params:** `limit` (1–20, default 12), `before_id` / `after_id` (exclusive cursors, mutually exclusive), deprecated `offset`/`order`, `sender_id`, `from_date`, `to_date` (`Y-m-d` or `Y-m-d H:i:s`; date-only `to_date` expands to 23:59:59).
* **Errors:** 400 invalid limit/cursor/combined cursors; 401; 403 non-participant; 404 conversation missing; 500.
* **Response:** envelope `data` = flat chronological array; top-level `conversation` header; top-level `pagination {limit, has_more, next_before_id, next_after_id}`. Empty → `data: []`, `has_more: false`. Withdrawal does **not** restrict reading.
* **Controller/service:** `message_controller_index()` → `message_service_list()`.

### 11.4 `POST /api/v1/conversations/{id}/messages`
* **Purpose:** send a message.
* **Body:** `{ "body": "…" }` (`message`/`content` aliases).
* **Validation:** non-empty, UTF-8, ≤ 5000 chars; conversation id must match URL; sender forced to authenticated user; attachment fields rejected.
* **Errors:** 401; 403 non-participant or withdrawn application; 404 conversation; 422 validation; 500.
* **Response:** 201, full message object. Outbox `message.created` emitted in-transaction.
* **Controller/service:** `message_controller_create()` → `message_service_create()`.

### 11.5 `PATCH /api/v1/conversations/{id}/messages/{message_id}`
* **Purpose:** edit own message, no time limit.
* **Body:** `{ "body": "…" }` (same validation as send).
* **Errors:** 401; 403 non-owner / non-participant / withdrawn; 404 message not in this conversation; 422 validation; 500.
* **Response:** 200 updated message (`is_edited=true`, `updated_at` set, id/created_at preserved). Outbox `message.updated`.
* **Controller/service:** `message_controller_edit()` → `message_service_edit()`.

### 11.6 `DELETE /api/v1/conversations/{id}/messages/{message_id}`
* **Purpose:** hard delete own message (delete for everyone), no time limit.
* **Errors:** 401; 403 non-owner / non-participant / withdrawn; 404 missing/already deleted/other conversation; 500.
* **Response:** 200, `data: null`. Outbox `message.deleted` (no body).
* **Controller/service:** `message_controller_delete_message()` → `message_service_delete_message()`.

### 11.7 WebSocket channel (not HTTP)
Documented in §9 and in `openapi.yaml` `x-websocket`. Connection: `ws://127.0.0.1:8081/?token=ACCESS_TOKEN`.

### 11.8 Documentation consistency check

| Source | Coverage |
|---|---|
| `openapi.yaml` | Documents exactly the 6 HTTP endpoints + `x-websocket`; detailed descriptions match code (limits, cursors, statuses, withdrawal semantics, deletion semantics) |
| `MASAR.json` | `Messaging` folder = exactly the 6 HTTP requests, consistent with routes |
| `docs/api/messaging.md` | Sections for list conversations, start conversation, list messages (cursor + header + status codes), send, edit (rules/real-time), delete (rules/real-time), withdrawal policy, transactional creation, lifecycle, error reference |
| Discrepancies | None for the routed surface. **Undocumented/unrouted extras:** mark-read, unread-count, search, reply, forward, attachments, show, legacy update/delete — present in code, absent from routes and docs (dead over HTTP). The `message_service_attachments()` path is broken (undefined repository function) and should not be exposed. |

---

## 12. Academic Status Architecture

### 12.1 Model

Profile-level state on the `students` row (not a separate table):

| Column | Type | Meaning |
|---|---|---|
| `academic_status` | `enum('student','graduate') NOT NULL DEFAULT 'student'` | current profile state |
| `graduation_date` | `date NULL` | full graduation date (YYYY-MM-DD) |
| `graduation_year` | `year NULL` | legacy/derived year (kept for backward compatibility) |
| `graduation_month` | `tinyint UNSIGNED NULL` | legacy/derived month 1–12 |
| `academic_certificate_file_id` | `bigint UNSIGNED NULL` | FK-by-convention to `files.id` |

Related enums/helpers: `app/shared/enums/academic_statuses.php` defines `ACADEMIC_STATUS_STUDENT='student'`, `ACADEMIC_STATUS_GRADUATE='graduate'`, `academic_statuses_all()`, `academic_status_is_graduate()`, study modes, graduation-month names/resolution. Loaded globally via `constants.php`'s guarded enum loader.

The enum file explicitly documents that this vocabulary is **NOT** interchangeable with `training_applications.applicant_type` (`'student'|'graduated'`), which is a per-application self-declaration. Training eligibility never reads `students.academic_status`.

Files: controller functions `student_academic_status_index/update/remove_certificate` (`app/modules/students/controllers/student_controller.php`, lines 660–694); service `student_account_academic_status()` / `student_account_update_academic_status()` / `student_account_upload_graduation_certificate()` / `student_account_remove_graduation_certificate()` / `student_account_resolve_graduation_certificate()`; validator `student_validator_academic_status()`; repository `student_repository_update_academic_status()` / `student_repository_find_academic_status()` / `student_repository_set_academic_certificate_file_id()`.

### 12.2 Routes (all behind `middleware_student()`)

| Method | Path | Controller |
|---|---|---|
| GET | `/api/v1/students/me/academic-status` | `student_academic_status_index()` |
| PUT | `/api/v1/students/me/academic-status` | `student_academic_status_update()` |
| DELETE | `/api/v1/students/me/academic-status/certificate` | `student_academic_status_remove_certificate()` |

The PUT controller reads `request_input()` plus `request_file('graduation_certificate')`; a custom multipart parser handles PUT bodies (PHP only populates `$_POST`/`$_FILES` for POST) — regression-guarded by Case 9 of the suite.

---

## 13. Student vs Graduate Behavior (requirement verification)

### 13.1 `student`
* Accepted and persisted; case-insensitive and trimmed (`Student`, ` STUDENT ` → `student`).
* Requires **no** graduation date, **no** certificate.
* Uploads nothing and never touches a stored certificate (switching graduate → student keeps the certificate on file and keeps the stored graduation date in the DB, but neither is returned).
* Response contains **only** `academic_status`.

### 13.2 `graduate`
* Requires `graduation_date` (YYYY-MM-DD) **and** `graduation_certificate` file; missing either → 422 with a field-specific error (`graduation_date` / `graduation_certificate`). A JSON-only graduate request cannot carry the file and is refused.
* Stores the date in `students.graduation_date` and derives/writes `graduation_year` + `graduation_month` from it.
* Uploads the certificate through the shared upload service (`folder 'certificates'`, `type = FILE_TYPE_ACADEMIC_CERTIFICATE = 'certificate_attachment'`) and points `students.academic_certificate_file_id` at the new file.
* Response contains `academic_status`, `graduation_date`, `graduation_certificate {id, file_name, file_url}`.

### 13.3 Unknown value
Rejected with 422 `errors.academic_status` naming the accepted values; the stored row is unchanged.

All of the above is verified by `tests/student_academic_status_regression.php` — **ALL PASS**, including "student response contains only academic_status", "graduate response contains exactly status, date and certificate", and "switching back to student does not remove the stored certificate".

---

## 14. Graduation Date

| Aspect | Actual implementation |
|---|---|
| Request field | `graduation_date` (canonical, `YYYY-MM-DD`); legacy `graduation_year` / `graduation_month` still honored as fallback |
| Validation | `DateTime::createFromFormat('Y-m-d')` round-trip must equal input; otherwise 422 `errors.graduation_date` |
| Required? | Yes when `academic_status = graduate` and no previously stored date is being kept; no for `student` |
| Storage | `students.graduation_date` (date); `graduation_year`/`graduation_month` derived from it via `DateTime::format('Y'/'n')` and written together |
| Update | `student_repository_update()` with allowlisted `graduation_date` for graduate requests |
| student → graduate | New date is required and stored; year/month derived |
| graduate → student | Old `graduation_date`/year/month and certificate **remain stored**; they are simply not returned. Nothing is cleared (confirmed by tests) |
| Response | `graduation_date` returned for graduates (string YYYY-MM-DD); not returned for students |

The repository comment explains the design: `graduation_month` pairs with the pre-existing `graduation_year` instead of a second competing date; migration 038 later introduced `graduation_date` while keeping year/month populated for backward compatibility.

---

## 15. Academic Certificate Upload and Deletion

### 15.1 Upload path
* Endpoint: the **same PUT** `/api/v1/students/me/academic-status`, multipart field `graduation_certificate` (there is no separate upload endpoint).
* Field name: `graduation_certificate` (controller `request_file('graduation_certificate')`).
* Allowed extensions: **pdf, png, jpg, jpeg** (`ACADEMIC_CERTIFICATE_EXTENSIONS`).
* Allowed MIME: `application/pdf`, `image/png`, `image/jpeg` (`ACADEMIC_CERTIFICATE_MIMES`).
* Max size: **5 MB** (`ACADEMIC_CERTIFICATE_MAX_SIZE`).
* Content checks (`student_account_validate_upload`): upload error, empty file, size, extension allowlist, path-traversal/executable-name rejection, `finfo` MIME sniffing, extension↔MIME agreement, magic-byte match. Client-declared `$_FILES['type']` is never trusted.
* Storage: shared upload service, `folder 'certificates'` (i.e. `storage/uploads/certificates`), randomized storage filename, typed `certificate_attachment`; the original name is retained in `files.original_name`.
* Reference: `students.academic_certificate_file_id`; the read path re-resolves ownership + type (`file_repository_find_for_user`, type must be `certificate_attachment`), so a dangling/foreign id can never be served.
* Replacement: uploading again stores a new file and repoints the reference (old file cleanup is the caller's/upload-service concern; no explicit orphan cleanup in this flow).
* Errors: 422 with specific messages for missing/unsupported/oversized/content-mismatched files; 500 for storage failures.

Separation from the old Profile CV storage: the old standalone "Academic Certificate" feature was removed (migration 038 header notes the column is now used exclusively by Academic Status); certificates go to the `certificates` folder, not the profile CV path. The academic-certificate constants live in `app/config/constants.php` (`FILE_TYPE_ACADEMIC_CERTIFICATE`, extensions/MIMEs/size).

### 15.2 Deletion — `DELETE /api/v1/students/me/academic-status/certificate`
1. Resolve student; if no `academic_certificate_file_id` → 404.
2. Re-verify the file belongs to this student and is typed `certificate_attachment`; if the reference is stale, clear it and return 404.
3. Validate the stored path is a safe storage path; `unlink()` the physical file (failure → 500).
4. In one transaction: clear `students.academic_certificate_file_id` and delete the `files` row; rollback on failure.
5. 200 on success; 404 when none exists; 401/403 per auth.

Verified by the suite (upload location, typed as academic certificate, read-back resolution, removal endpoint behavior).

---

## 16. Get My Academic Status Response

Actual behavior (`student_account_academic_status`, lines 417–456):

**student:**

```json
{ "success": true, "message": "Academic status updated successfully.",
  "data": { "academic_status": "student" }, "errors": null }
```

**graduate:**

```json
{ "success": true, "message": "Academic status updated successfully.",
  "data": {
    "academic_status": "graduate",
    "graduation_date": "2026-06-15",
    "graduation_certificate": { "id": 123, "file_name": "graduation_certificate.pdf",
                                "file_url": "http://localhost/Masar/backend/api/v1/files/123?download=true" }
  },
  "errors": null }
```

Legacy fields **`is_graduate`, `graduation_year`, `graduation_month`, `graduation_month_name` are NOT returned** — confirmed in code and by the suite's "no legacy graduation fields are exposed". `graduation_certificate` is present only when a certificate is on file and resolves to an owned academic-certificate file.

This matches the intended response contract documented in `openapi.yaml` (lines 549–664) and `docs/api/students.md`.

---

## 17. Education and Skills Relationship (separate concepts)

Academic Status is independent from Education records and Skills:

| Concept | Storage | Key fields | Notes |
|---|---|---|---|
| Academic Status | `students.academic_status`, `graduation_date`, certificate ref | student/graduate + date + certificate | single profile state; certificate is a file reference |
| Education | separate `student_educations` records via `student_education_service` | degree (lookup id), university/faculty/field (numeric lookup ids), start/end year, study mode | CRUD endpoints `GET/POST/PUT/DELETE /students/me/education[/{id}]`; `STUDENT_MAX_EDUCATIONS = 20`; unknown lookups refused |
| Skills | normalized relation via `student_profile_repository` skill sync | numeric skill ids resolved from names | `GET/POST/PUT/DELETE /students/me/skills`; `STUDENT_MAX_SKILLS = 50`; unknown skill names reported |

Details worth recording:
* `degree_name` may carry the numeric **degree lookup id**; `degree_id` is accepted as a legacy alias; both resolve through the `degrees` lookup and keep a human-readable `degree_name` in sync (lines 271–310 of `student_education_service.php`).
* University/faculty/field are resolved to numeric ids; a faculty is validated against its owning university.
* Skills are matched to existing skill lookup ids by name; additions/updates sync the relation (replace-all supported).

These three features share the student profile but have separate tables, validators and endpoints — no merging occurred.

---

## 18. Complete Academic Status API Reference

Base `http://localhost/Masar/backend/api/v1`; all require a **student** Bearer token (`middleware_student()`).

### 18.1 `GET /students/me/academic-status`
* Returns `{ academic_status }` for students; adds `graduation_date` + `graduation_certificate` for graduates.
* Errors: 401, 403, 404 (profile missing).
* Controller `student_academic_status_index()` → `student_account_academic_status()`.

### 18.2 `PUT /students/me/academic-status`
* Body: `application/json { "academic_status": "student" }` for status-only; `multipart/form-data` with `academic_status`, optional `graduation_date`, optional `graduation_certificate` for graduate updates (method must stay PUT).
* Validation: status ∈ {student, graduate} (case-insensitive, trimmed); graduate needs `graduation_date` (YYYY-MM-DD) and a certificate file; month/year fallbacks validated when present.
* Success: 200 with the fresh status payload; 422 with field errors; 401/403; 500.
* Controller `student_academic_status_update()` → `student_account_update_academic_status()`.

### 18.3 `DELETE /students/me/academic-status/certificate`
* Deletes the student's graduation certificate (physical file + file record + reference) as described in §15.2.
* 200 success; 404 none/stale; 401/403; 500.
* Controller `student_academic_status_remove_certificate()` → `student_account_remove_graduation_certificate()`.

### 18.4 Documentation consistency
* `openapi.yaml` documents all three endpoints with correct semantics (status enum, graduate requirements, encoding note about PUT multipart, response shapes, legacy-field removal).
* `MASAR.json` Students → Academic Status contains exactly these three requests.
* `docs/api/students.md` mirrors them with examples.
* No discrepancies found for the academic-status surface.

---

## 19. Database Tables and Relationships

### 19.1 Messaging

**`conversations`** (schema line 1415; live-verified):

| Column | Type | Notes |
|---|---|---|
| id | bigint UNSIGNED PK | |
| student_id | bigint UNSIGNED NOT NULL | → students.id (index `idx_conversations_student`) |
| company_id | bigint UNSIGNED NOT NULL | → companies.id (index `idx_conversations_company`) |
| application_id | bigint UNSIGNED NOT NULL **UNIQUE** | → training_applications.id; enforces one conversation per application |
| created_at / updated_at | datetime | `updated_at` bumped on each new message |

**`messages`** (line 1618):

| Column | Type | Notes |
|---|---|---|
| id | bigint UNSIGNED PK | chronological order = id order |
| conversation_id | bigint UNSIGNED NOT NULL | index `(conversation_id, created_at)`, `(conversation_id, id)`, `(conversation_id, read_at, sender_user_id)` |
| sender_user_id | bigint UNSIGNED NOT NULL | index `idx_messages_sender` |
| body | text NOT NULL | |
| is_read | tinyint(1) default 0 | unread = `read_at IS NULL` |
| is_edited | tinyint(1) default 0 | added by migration 041 |
| read_at | datetime NULL | |
| created_at | datetime NOT NULL | |
| updated_at | datetime NULL | added by migration 041; set on edit |

**`message_events`** (transactional outbox; migration 040 + schema line 1866):

| Column | Type | Notes |
|---|---|---|
| id | bigint UNSIGNED PK AUTO_INCREMENT | |
| message_id | bigint UNSIGNED | **no unique key** after migration 041 (one message can produce created/updated/deleted events) |
| conversation_id | bigint UNSIGNED | |
| sender_user_id | bigint UNSIGNED | also used as `deleted_by` in delete events |
| body | text | empty for `message.deleted` |
| created_at | datetime NOT NULL | |
| dispatched_at | datetime NULL | pending until the WS server marks it |
| event_type | varchar(32) default 'message.created' | `message.created` / `message.updated` / `message.deleted`; index `(dispatched_at, id)` |

Relationships: `conversations` 1—N `messages` 1—N `message_events` (by conversation/message ids; no FK constraints declared in the dump — associations are enforced in code). Conversations link to `training_applications`, which links to `training_listings`/`students`/`companies`.

Migrations:
* `039_add_messaging_performance_indexes.sql` — adds `idx_messages_conversation_id (conversation_id, id)` and `idx_messages_unread (conversation_id, read_at, sender_user_id)`.
* `040_create_message_events_outbox.sql` — creates `message_events` (initially with `UNIQUE KEY uq_message_events_message (message_id)` and `idx_message_events_pending`).
* `041_add_message_edit_fields.sql` — adds `messages.is_edited` / `messages.updated_at`; drops the unique key on `message_events.message_id`.

> **Discrepancy found (reported, not fixed):** migration 041's header says it adds `message_events.event_type`, and the live database **does** have `event_type varchar(32) NOT NULL DEFAULT 'message.created'` (verified via `SHOW COLUMNS`, and the schema dump includes it), but the migration file itself contains only the two `ALTER TABLE` statements shown above — **no committed migration adds `event_type`**. Any environment rebuilt purely from `040`+`041` would lack the column that `message_event_repository_create()` inserts into. This should be reconciled in a future migration/doc fix.

### 19.2 Students / Academic Status

Columns listed in §12.1. Related tables: `students` → `files` (via `academic_certificate_file_id`, `cv_file_id`, `profile_image_file_id`), `training_applications`, `student_educations`, skill relation tables.

Migrations:
* `035_add_student_profile_account_and_academic_fields.sql` — adds `students.academic_status` (enum student/graduate, default student), `graduation_month`, `academic_certificate_file_id` (and username/profile columns).
* `038_add_graduation_date.sql` — adds `graduation_date date`; states the legacy year/month are kept and derived; notes the standalone Academic Certificate feature was removed and the column reused by Academic Status.

Indexes: `idx_students_academic_certificate_file` on the certificate reference (schema line 4590).

---

## 20. Complete Relevant Files Inventory

### 20.1 Messaging

| File | Responsibility | Relevant functionality | Git history |
|---|---|---|---|
| `routes/messaging.php` | HTTP guards | 6 endpoints only | modified in bulk sync commits |
| `app/modules/messaging/controllers/conversation_controller.php` | Conversation HTTP layer | index/create + unrouted mark-read/unread/search/etc. | bulk commits |
| `app/modules/messaging/controllers/message_controller.php` | Message HTTP layer | index/create/edit/delete_message + unrouted extras; send rate limit | bulk commits |
| `app/modules/messaging/services/conversation_service.php` | Business rules | access, withdrawal block, list/create | bulk commits |
| `app/modules/messaging/services/message_service.php` | Business rules | send/edit/delete + dead attachments path | bulk commits |
| `app/modules/messaging/repositories/conversation_repository.php` | SQL | list w/ subqueries, create, meta, read/unread | bulk commits |
| `app/modules/messaging/repositories/message_repository.php` | SQL | cursor list, transactional create/edit/delete | bulk commits |
| `app/modules/messaging/repositories/message_event_repository.php` | Outbox SQL | create/pending/dispatched/discard/baseline | added in latest sync (previously untracked) |
| `app/modules/messaging/validators/conversation_validator.php` | Query validation | list filters | bulk commits |
| `app/modules/messaging/validators/message_validator.php` | Input validation | body 5000, page limits, unsupported fields | bulk commits |
| `app/modules/messaging/websocket/websocket_server.php` | WS server | auth, handshake, event loop, outbox relay | added in latest sync |
| `app/modules/messaging/websocket/websocket_hub.php` | Connection registry | subscriptions, baseline broadcast, flush | added in latest sync |
| `app/modules/messaging/websocket/websocket_protocol.php` | Frame codec | handshake, encode/decode | added in latest sync |
| `websocket/server.php` | CLI entry | `php websocket/server.php` | added in latest sync |
| `database/migrations/039…041` | Schema | indexes, outbox, edit fields | added in latest sync |
| `docs/api/messaging.md`, `docs/WEBSOCKET.md` | Docs | endpoint + WS contract | messaging.md modified; WEBSOCKET.md added |
| `tests/messaging_*.php`, `tests/conversations_list_*.php` | Regression suites | see §22 | several added in latest sync |
| `openapi.yaml`, `MASAR.json` | Contracts | Messaging paths, x-websocket, 6-request folder | modified in latest sync |

Notifications/upload handling: no messaging-specific notification or upload files exist (features absent).

### 20.2 Academic Status

| File | Responsibility | Relevant functionality | Git history |
|---|---|---|---|
| `routes/students.php` | Guards | 3 academic-status endpoints | modified |
| `app/modules/students/controllers/student_controller.php` | HTTP layer | index/update/remove_certificate (lines 660–694) | modified |
| `app/modules/students/services/student_account_service.php` | Business rules | status read/update, cert upload/remove/resolve, upload validation, year/month normalization | modified |
| `app/modules/students/repositories/student_repository.php` | SQL | update_academic_status, find_academic_status, set certificate ref | modified |
| `app/modules/students/validators/student_validator.php` | Validation | `student_validator_academic_status` (status/month/year/date) | modified |
| `app/shared/enums/academic_statuses.php` | Vocabulary | statuses, month helpers, study modes | loaded globally |
| `app/config/constants.php` | Constants | academic certificate extensions/MIMEs/size, `FILE_TYPE_ACADEMIC_CERTIFICATE` | reorganized |
| `database/migrations/035…038` | Schema | status/month/certificate column, graduation_date | added earlier |
| `tests/student_academic_status_regression.php` | Suite | 12 cases, 52 checks | added in latest sync |
| `docs/api/students.md`, `openapi.yaml`, `MASAR.json` | Docs | academic-status contract | modified |

Related (kept separate): `student_education_service.php`, `student_education_repository.php`, `student_profile_service.php` (skills), `student_profile_repository.php`, `student_validator.php` skills rules.

---

## 21. Git History and Actual Change History

Git history for these paths consists of **bulk sync commits**, not granular feature commits:

Messaging-related commits (newest first):
`3b8ee69` chore: sync local project with latest dev · `c61bb82` chore: sync local project with latest dev · `e876099` replace backend with local version · `9673c93` replace backend with local version · `646e864` Update MASAR backend.

Students/Academic-Status-related commits add:
`0c026a9` Update backend only · `e9db0b2` Merge backend changes · `b347d36` Update backend · `c598d17` Update backend · `a60f024` Update backend · `74fd9d7` Update.

Consequences for the report:
* Per-feature attribution ("which commit introduced edit/delete") is **not recoverable** from this history; I have therefore documented current implementation and file state rather than inventing commit-level narratives.
* The messaging WebSocket components, outbox repository, migrations 039–041 and several test files entered the repository in the most recent sync (`3b8ee69`); before that they were untracked working-tree files.
* `backend/database/schema/masar.sql` was refreshed in `6a93138` ("chore(db): refresh schema dump to match the student profile migrations") and now includes `graduation_date`/`academic_status` and the `message_events.event_type` column.

---

## 22. Test Results (actually executed)

All commands run directly (`php tests/<suite>.php`) against the live seeded `masar` database.

| Suite | Area | Result |
|---|---|---|
| `student_academic_status_regression.php` | Academic status: student/graduate, validation, normalization, certificate upload/reject/read-back, PUT multipart, response shapes, graduate→student retention | **ALL PASS** (52 checks) |
| `messaging_edit_delete_regression.php` | Live WS server: connect/auth, subscribe authz, send, `message.created`, edit + `message.updated`, hard delete + `message.deleted` (no body), unauthorized isolation | **ALL PASS** |
| `messaging_conversation_meta_regression.php` | Company header `{id, company{id,name,image}}`, null-image case, 403 non-participant, data/pagination untouched | **ALL PASS** |
| `messaging_pagination_regression.php` | Default 12, cursor before/after, validation 400s, consecutive-page completeness, conversation scoping, index usage | **ALL PASS** |
| `messaging_websocket_regression.php` | Handshake 401/101, subscription authz, baseline suppression, single delivery, no token leakage, withdrawal 403 + no event, downtime/outbox/restart recovery via `after_id` | **ALL PASS** |
| `conversations_list_default_limit_regression.php` | Conversations list default limit 20, explicit limit, 400 on 0/101 | **ALL PASS** |
| `conversations_list_company_image_regression.php` | `company_image` on every item, matches company logo, null case, association correctness | **ALL PASS** |

Known pre-existing failure (unrelated, not chased): `tests/training_dates_normalization_regression.php` has stale dataset expectations (per `AGENTS.md`).

Missing coverage worth noting:
* No automated suite exercises the **unrouted** messaging endpoints (mark-read, unread count, search, reply, forward, attachments) — because they cannot be reached over HTTP.
* No suite asserts **notification absence/presence** for messaging (feature not implemented).
* `composer test`/PHPUnit executes zero tests (procedural suites only) — do not treat it as behavioral coverage.

---

## 23. Implemented vs Partially Implemented vs Missing; Known Issues & Recommendations

### 23.1 Implemented and verified
* Conversation creation for accepted applications, idempotent, participant-derived.
* Conversation listing with filters, pagination, last message, unread counts, company image.
* Message send (transactional + outbox), cursor pagination, edit (no time limit), hard delete (delete-for-everyone, no time limit).
* Withdrawal policy: read stays open; send/edit/delete blocked 403 (DB-fresh check).
* Real RFC 6455 WebSocket server: JWT handshake auth, per-subscribe authorization, baseline suppression, `message.created`/`updated`/`deleted` events, ping/pong, downtime recovery via `after_id`, `x-websocket` docs.
* Academic status student/graduate, graduation date storage/derivation, certificate upload with strict validation, certificate deletion, exact response shapes, legacy fields excluded.

### 23.2 Partially implemented / dead code
* **Attachments:** controller/service/validator fragments exist; no storage, no columns, no route, and `message_service_attachments()` calls an undefined `message_repository_attachments()` (fatal if invoked). Treat as not implemented; do not expose.
* **Mark-read / unread-count / search / reply / forward / show / legacy update+delete / dispatchers:** fully written in controllers/services/repositories but **not routed**; consumers cannot reach them. Decide deliberately: route them or remove them.
* **Notifications on message send:** constant + mail preference exist, no creation path.

### 23.3 Missing / gaps
* **No HTTP read/unread endpoints** despite implemented backend logic.
* **Migration gap:** `message_events.event_type` exists in the live DB and schema dump but no committed migration adds it (041's header claims it). A fresh build from migrations would break `message_event_repository_create()`.
* No automated coverage for the unrouted surfaces.

### 23.4 Recommendations (not applied — read-only task)
1. Add a migration that safely adds `message_events.event_type` (or correct 041) so schema rebuilds match the dump.
2. Decide the fate of the unrouted messaging actions: expose the ones the product needs (mark-read, unread count, search are natural fits) and delete or clearly isolate the rest; fix or remove the broken attachments path.
3. If in-app notifications for new messages are desired, wire `notification_service_notify_user()` (type `new_message`) into `message_service_create` and flip the `mail.php` preference; document it.
4. Consider returning read/unread through a routed endpoint, since the UI needs unread badges and the logic already exists.

---

*End of report. Prepared from the actual source tree, live database schema/index inspection, API contracts and seven executed regression suites. No files other than this report were created or modified.*

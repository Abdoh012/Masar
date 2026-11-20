# MASAR API — Messaging

## Overview

The Messaging API exposes the conversations a student and a company share once a
training application has been accepted, plus the messages inside those
conversations.

Base URL:

```text
/api/v1
```

| Method | Path | Purpose |
| ------ | ---- | ------- |
| `GET`  | `/conversations` | List the authenticated user's conversations |
| `POST` | `/conversations` | Start (or return) the conversation of an accepted application |
| `GET`  | `/conversations/{id}/messages` | Read a conversation's messages |
| `POST` | `/conversations/{id}/messages` | Send a message |

Every endpoint requires a valid access token. Real-time events are delivered
over a separate WebSocket connection documented in
[WEBSOCKET.md](../../WEBSOCKET.md); the HTTP API remains the source of truth for
persistence.

---

# Authentication

```http
Authorization: Bearer ACCESS_TOKEN
Accept: application/json
```

The authenticated user is derived from the token. A client can never choose the
sender, the participants, or the conversation of a message:

* The sender is always the authenticated user.
* The student and company of a conversation are always resolved from the
  training application.

A request without a valid token is refused with `401`.

---

# Authorization model

A conversation belongs to exactly two people: the student and the company of one
accepted training application. There are no group conversations and no arbitrary
participants.

* A user who is neither the student nor the company gets `403`.
* A conversation that does not exist gets `404`.
* Ownership is re-checked against the database on every request; the token's
  role/claims are not trusted as authorization state.

Withdrawal of the application changes sending only, never reading — see
[Withdrawal policy](#withdrawal-policy).

---

# Response envelope

Every response uses the standard envelope:

```json
{
  "success": true,
  "message": "Messages retrieved successfully.",
  "data": [ ],
  "errors": null
}
```

`success` is derived from the HTTP status code. `errors` is an object keyed by
field on validation failures; each value is a human-readable string.

---

# 1. List My Conversations

```http
GET /api/v1/conversations?limit=20&offset=0
```

Returns every conversation in which the authenticated user is the student or the
company.

### Query parameters

| Name | Type | Default | Description |
| ---- | ---- | ------- | ----------- |
| `limit` | integer | `20` | Page size. Must be an integer between 1 and 100. |
| `offset` | integer | `0` | Rows to skip. Must be an integer >= 0. |
| `application_id` | integer | – | Only the conversation of this application. An application the user is not part of yields an empty page, not an error. |
| `keyword` | string | – | LIKE filter over the student name, company name and training title. |

Values are read from the query string, never from a request body. Malformed or
out-of-range values are rejected with `400` and the offending field in `errors`
(`limit`: 1-100, `offset`: >= 0).

### Response

`data` is an object with the page of rows and the pagination metadata:

```json
{
  "items": [
    {
      "id": 225,
      "student_id": 1458,
      "company_id": 100144,
      "application_id": 1671,
      "created_at": "2026-08-01 11:30:00",
      "updated_at": "2026-08-13 10:00:00",
      "student_name": "…",
      "company_name": "…",
      "company_image": "company-logo-test/data.png",
      "training_title": "…",
      "unread_count": 1,
      "last_message": {
        "id": 1108,
        "body": "Just bring your laptop and any portfolio pieces you mentioned.",
        "sender_user_id": 100568,
        "created_at": "2026-08-17 11:00:00"
      },
      "application_status": "accepted",
      "training": {
        "title": "Junior PHP Developer Track",
        "starts_at": "2026-08-10 09:00:00",
        "ends_at": "2026-08-27 17:00:00"
      }
    }
  ],
  "pagination": {
    "total": 3,
    "limit": 20,
    "offset": 0
  }
}
```

Additional fields per row:

* `company_image`: the conversation's company's profile image as the relative
  storage path stored in `companies.company_logo` (e.g.
  `company-logo-test/data.png`), or `null` when the company has no logo.
  Present on every row.
* `last_message`: the conversation's latest message — `id`, `body`,
  `sender_user_id` and `created_at` — or `null` when the conversation has no
  messages. The latest message is the newest by `created_at` then `id`.
* `application_status`: the status of the linked training application, read
  live from the database (e.g. `accepted`, `submitted`, `rejected`,
  `withdrawn`).
* `training`: the linked training listing as `title`, `starts_at` and
  `ends_at` (actual `training_listings` columns). Dates are returned as
  stored and are `null` when the listing has none; the whole object is `null`
  when no training listing is linked.

`pagination.total` is the number of the user's conversations before paging,
using the same visibility and filters as the list. It is the same whether or
not `limit`/`offset` are given, and empty pages still return it.

`unread_count` counts messages sent by the **other** participant that have not
been read yet, and appears only on this endpoint.

---

# 2. Start a Conversation

```http
POST /api/v1/conversations
Content-Type: application/json
```

### Request body

```json
{
  "application_id": 2009
}
```

| Field | Required | Description |
| ----- | -------- | ----------- |
| `application_id` | yes | The accepted training application to open a conversation for. |

Only `application_id` is used. `title`, `name`, `participant_id`,
`participant_ids` and `participants` are **rejected with `422`** because the
`conversations` table cannot persist them; accepting them would silently discard
client data. Group conversations are not supported.

The student and the company are read from the application and its training —
they are never taken from the request body.

### Business rules

| Condition | Status |
| --------- | ------ |
| Missing / non-numeric / non-positive `application_id` | `422` |
| Application does not exist | `404` |
| Application status is not `accepted` | `422` |
| Authenticated user is neither the application's student nor its company | `403` |
| A conversation already exists for the application | `200` |
| Conversation created | `201` |

`conversations.application_id` is unique, so the endpoint is idempotent:

* New conversation → `201`, `message = "Conversation created successfully."`
* Existing conversation → `200`, `message = "Conversation already exists."`, and
  the existing row is returned unchanged.

Two concurrent requests for the same application resolve to the same single
conversation: the losing insert hits the unique key and returns the winner
instead of failing.

---

# 3. List Messages

```http
GET /api/v1/conversations/{id}/messages
```

`data` is a flat array ordered by the auto-increment `id`, oldest to newest.
Cursor pagination is bounded: a request never reads the whole conversation into
memory. Pagination metadata is returned as a sibling of `data` in the standard
envelope (see below).

### Query parameters

Query parameters are read from the query string only — never from a request
body — and are validated. An invalid value returns `400` with the offending field
in `errors`.

| Name | Type | Default | Rules |
| ---- | ---- | ------- | ----- |
| `limit` | integer | `12` | `1`–`20`; `0`, `21`, negative or non-numeric → `400` |
| `before_id` | integer | – | Exclusive: only messages with `id` lower than this value; must be a positive integer |
| `after_id` | integer | – | Exclusive: only messages with `id` greater than this value; must be a positive integer |
| `offset` | integer | `0` | Legacy parameter, `>= 0`; negative or non-numeric → `400` |
| `order` | string | `asc` | Legacy display override, `asc` or `desc`; anything else → `400` |
| `sender_id` | integer | – | Legacy filter: only messages sent by this user id |
| `from_date` | string | – | Legacy filter: `Y-m-d` or `Y-m-d H:i:s`; lower bound on `created_at` |
| `to_date` | string | – | Legacy filter: `Y-m-d` or `Y-m-d H:i:s`; upper bound on `created_at` |

`before_id` and `after_id` are mutually exclusive. Supplying both returns `400`
with `errors.before_id_after_id`. A non-numeric or non-positive cursor is a `400`;
a positive cursor that does not exist is safe and simply yields the nearest page.

A date-only `to_date` covers the whole day: it is expanded to `23:59:59` of that
day. Date-only `from_date` starts at `00:00:00`. Any other date format is a `400`.

Examples:

```http
GET /api/v1/conversations/225/messages                       # latest 12, chronological
GET /api/v1/conversations/225/messages?limit=20              # up to 20
GET /api/v1/conversations/225/messages?limit=12&before_id=950 # older messages
GET /api/v1/conversations/225/messages?limit=12&after_id=950  # newer messages
```

### Response

```json
{
  "success": true,
  "message": "Messages retrieved successfully.",
  "data": [
    {
      "id": 1104,
      "conversation_id": 225,
      "sender_user_id": 100568,
      "body": "Welcome aboard!",
      "is_read": 1,
      "is_edited": 0,
      "read_at": "2026-08-14 12:00:00",
      "created_at": "2026-08-13 11:00:00",
      "updated_at": null,
      "sender_id": 100568
    }
  ],
  "conversation": {
    "id": 225,
    "company": {
      "id": 100144,
      "name": "NileTech Solutions",
      "image": null
    }
  },
  "pagination": {
    "limit": 12,
    "has_more": true,
    "next_before_id": 1104,
    "next_after_id": 1108
  },
  "errors": null
}
```

`sender_id` is an alias of `sender_user_id`. Sender email addresses are not
exposed.

### Conversation header

A top-level `conversation` object gives the UI a WhatsApp-style header
(company name + image) to render once above the message list. It is separate
from the `data` messages array and is never repeated inside message objects.

| Field | Type | Description |
| ----- | ---- | ----------- |
| `conversation.id` | integer | The conversation id (same as the URL id). |
| `conversation.company.id` | integer | The company linked to the conversation. |
| `conversation.company.name` | string | The actual company name (`companies.legal_name`). |
| `conversation.company.image` | string / null | The company's profile image path from `companies.company_logo`, or `null` when the company has no logo. |

Facts:

* Both `student_id` and `company_id` are required on the `conversations`
  schema, so `company` is populated whenever the conversation exists; it is
  `null` only if the linked company row cannot be resolved.
* The metadata is fetched with **one** scoped query per request
  (`LEFT JOIN companies ON companies.id = conversations.company_id`); there
  is no N+1 and no extra company fields are read.
* The image uses the same value source as other MASAR APIs
  (`companies.company_logo`, the relative storage path produced by the file
  upload service).
* Company information for unauthorized conversations is never exposed:
  access is checked with `conversation_service_access()` before the header is
  built.

`data` remains a flat array for backward compatibility. The `pagination` object
sits beside it:

| Field | Meaning |
| ----- | ------- |
| `limit` | Effective page size (1–20). |
| `has_more` | Another page exists in the direction being traversed. Determined with a `limit + 1` read; the extra row is never returned. |
| `next_before_id` | Oldest id in the page; pass as `before_id` to fetch older messages. |
| `next_after_id` | Newest id in the page; pass as `after_id` to fetch newer messages. |

Pagination modes:

* **Default** — query `ORDER BY id DESC LIMIT limit + 1` (served by
  `idx_messages_conversation_id`), reverse to chronological order. `has_more`
  means older messages exist.
* **`before_id`** — `id < cursor`, scanned newest-first, returned chronologically.
* **`after_id`** — `id > cursor`, scanned oldest-first, returned chronologically;
  this is the cursor a disconnected WebSocket client uses to catch up.

The cursor row itself is always excluded, and `id` is the deterministic
tie-breaker. Empty conversations return `data: []` with `has_more: false`.

### Status codes

| Condition | Status |
| --------- | ------ |
| Invalid query parameter | `400` |
| Not authenticated | `401` |
| Conversation exists but user is not a participant | `403` |
| Conversation does not exist | `404` |
| Success | `200` |

Each message carries `is_edited` (`0`/`1`) and `updated_at` (`null` when never
edited). `is_edited` becomes `1` when the message is edited; `updated_at` is
set at that moment.

---

# 4a. Edit a Message

```http
PATCH /api/v1/conversations/{conversation_id}/messages/{message_id}
Content-Type: application/json
```

### Request body

```json
{ "body": "Updated message content." }
```

| Field | Required | Description |
| ----- | -------- | ----------- |
| `body` | yes | New message text. `message` and `content` are accepted as aliases. |

### Rules

* Any message owner can edit, with **no time limit**.
* The sender is re-read from the database using the message id **and** the URL
  conversation id. Never trust a client-provided sender id.
* Empty or whitespace-only content → `422` with `errors.body`. Same length and
  UTF-8 rules as message creation (5000 characters max, trimmed).
* Returns the updated message via the standard success envelope.
* `id` and the original `created_at` are never modified; `is_edited` becomes
  `1`; `updated_at` is set to the current timestamp.

### Status codes

| Condition | Status |
| --------- | ------ |
| Success | `200` |
| Not authenticated | `401` |
| Conversation exists but user is not a participant | `403` |
| Message does not exist in this conversation | `404` |
| Not the sender, or application is withdrawn | `403` |
| Validation failed (empty body, etc.) | `422` |

### Real-time

After the transaction commits, the participant(s) receive
[`message.updated`](../../docs/WEBSOCKET.md) via WebSocket.

---

# 4b. Delete a Message (Delete for Everyone)

```http
DELETE /api/v1/conversations/{conversation_id}/messages/{message_id}
```

### Rules

* Hard delete: the row is physically removed from the `messages` table.
* No soft delete, no placeholder, no `deleted_at`, no archive.
* Only the original sender (re-read from the database, never trusted from the
  client) can delete.
* After deletion the message no longer appears in message retrieval, no longer
  counts as unread, and no longer becomes the conversation's `last_message`.

### Status codes

| Condition | Status |
| --------- | ------ |
| Success | `200` |
| Not authenticated | `401` |
| Conversation exists but user is not a participant | `403` |
| Message does not exist (or was already deleted) | `404` |
| Not the sender, or application is withdrawn | `403` |

### Real-time

After the transaction commits, the participant(s) receive
[`message.deleted`](../../docs/WEBSOCKET.md) — it contains only `message_id`,
`conversation_id` and `deleted_by`, never the deleted body.

---

# 4. Send a Message

```http
POST /api/v1/conversations/{id}/messages
Content-Type: application/json
```

### Request body

```json
{
  "body": "Hello, thank you for accepting my application."
}
```

| Field | Required | Description |
| ----- | -------- | ----------- |
| `body` | yes | Message text. `message` and `content` are accepted as aliases. |
| `conversation_id` | no | Optional. Must match the URL id when present. |

The conversation is taken from the URL and is authoritative. Omitting
`conversation_id` from the body is normal; sending a **different** value is
refused with `422` so a message can never be redirected by the body.

### Validation rules

* `body` is trimmed before validation.
* Missing, empty or whitespace-only → `422` with `errors.body`.
* More than **5000 characters** → `422`.
* Invalid UTF-8 → `422`.
* `sender_user_id`, `sender_id` and `user_id` are not accepted as authority:
  values that differ from the authenticated user are `422`; identical values are
  ignored.
* `message_type`, `reply_to_id`, `attachment_ids`, `attachments` and `file_id`
  are not persisted by this API and are **rejected with `422`** rather than
  silently ignored.

### Status codes

| Condition | Status |
| --------- | ------ |
| Not authenticated | `401` |
| Conversation does not exist | `404` |
| Not a participant / not authorized for the application | `403` |
| Linked application was withdrawn | `403` (see below) |
| Body or sender validation failed | `422` |
| Message stored | `201` |
| Unexpected database failure | `500` (safe, generic message) |

### Response

`data` is the stored message, in the same shape as the list endpoint. The success
message is `Message sent successfully.`

---

# Withdrawal policy

When the training application linked to a conversation is `withdrawn`:

* **Reading is always allowed** for both participants:
  `GET /conversations/{id}/messages` keep working.
* The conversation and its entire message history are kept; nothing is deleted.
* **Sending is blocked for both participants**, with `403` and:

```json
{
  "success": false,
  "message": "Messaging is disabled because the training application was withdrawn.",
  "data": null,
  "errors": {
    "application_id": "Messaging is disabled because the training application was withdrawn."
  }
}
```

The application status is re-read from the database on **every** send attempt, so
the block takes effect immediately and is not affected by anything cached in the
conversation row or the token. Other statuses keep their previous behavior; only
`withdrawn` blocks sending.

---

# Transactional message creation

Inserting a message and updating `conversations.updated_at` happen inside one
database transaction:

1. `INSERT INTO messages …`
2. `UPDATE conversations SET updated_at = CURRENT_TIMESTAMP …`
3. `INSERT INTO message_events …` (outbox row for the real-time `message.created` event)

All three statements commit together. If any statement fails, the whole
transaction is rolled back — a message is never stored without its event, and a
real-time event is never broadcast for a message that failed to persist.
Diagnostics go to the database log; the client only ever receives the generic
`Unable to send message.` with `500`, never SQL or exception details.

Message sending is rate limited per authenticated user through the shared
security helper (60 requests / minute when rate limiting is enabled). WebSocket
handshakes are rate limited per IP (30 / minute).

---

# Message lifecycle (current phase)

This phase implements reading and sending only.

Not implemented yet — do not rely on, and do not document as working:

* Attachments and message types (`message_type`, `reply_to_id`, `file_id`).
* Editing or deleting messages.
* Archiving, renaming or restoring conversations.
* Marking messages read / unread endpoints.
* Message or conversation search.
* Group conversations or arbitrary participants.
* Push notifications outside the WebSocket connection.

Real-time `message.created` delivery **is** implemented — see
[WebSocket events](../../WEBSOCKET.md).

Message rows carry `is_read` / `read_at` columns, but no endpoint in this phase
writes them, and `unread_count` on the conversation list is the only read-state
surface.

---

# Error reference

| Status | Meaning in this module |
| ------ | ---------------------- |
| `400` | Malformed query parameters (messages list) |
| `401` | Missing / invalid access token |
| `403` | Not a participant, unauthorized application, or withdrawn application on send |
| `404` | Conversation or application not found |
| `422` | Body validation failure, unsupported field, or non-accepted application |
| `429` | Too many messages sent / too many connection attempts |
| `500` | Unexpected server/database failure — safe generic message only |

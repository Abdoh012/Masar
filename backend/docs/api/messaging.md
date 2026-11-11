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
| `GET`  | `/conversations/{id}` | Read one conversation |
| `GET`  | `/conversations/{id}/messages` | Read a conversation's messages |
| `POST` | `/conversations/{id}/messages` | Send a message |

Every endpoint requires a valid access token.

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
GET /api/v1/conversations
```

Returns every conversation in which the authenticated user is the student or the
company.

### Query parameters

| Name | Type | Default | Description |
| ---- | ---- | ------- | ----------- |
| `application_id` | integer | – | Only the conversation of this application. An application the user is not part of yields an empty array, not an error. |
| `keyword` | string | – | LIKE filter over the student name, company name and training title. |
| `limit` | integer | `50` | Page size, clamped to `100`. |
| `offset` | integer | `0` | Rows to skip. |

These filters are optional. They are not validated the way the message filters
are: out-of-range values are clamped rather than rejected.

### Response

`data` is a flat array (no pagination envelope). Each row is a conversation:

```json
{
  "id": 225,
  "student_id": 1458,
  "company_id": 100144,
  "application_id": 1671,
  "created_at": "2026-08-01 11:30:00",
  "updated_at": "2026-08-13 10:00:00",
  "student_name": "…",
  "company_name": "…",
  "training_title": "…",
  "unread_count": 1
}
```

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

# 3. Get a Conversation

```http
GET /api/v1/conversations/{id}
```

| Condition | Status |
| --------- | ------ |
| Not authenticated | `401` |
| Conversation does not exist | `404` |
| Conversation exists, user is not a participant (or is no longer authorized for the application) | `403` |
| Success | `200` |

`data` is one conversation row with the joined `student_name`, `company_name`
and `training_title` fields.

Reading is not blocked by a withdrawn application.

---

# 4. List Messages

```http
GET /api/v1/conversations/{id}/messages
```

`data` is a flat array (no pagination envelope), ordered by `created_at` and then
`id`.

### Query parameters

Query parameters are read from the query string only — never from a request
body — and are validated. An invalid value returns `400` with the offending field
in `errors`.

| Name | Type | Default | Rules |
| ---- | ---- | ------- | ----- |
| `limit` | integer | `50` | `1`–`100`; `0`, `101` or non-numeric → `400` |
| `offset` | integer | `0` | `>= 0`; negative or non-numeric → `400` |
| `order` | string | `asc` | `asc` or `desc`; anything else → `400` |
| `before_id` | integer | – | Exclusive: only messages with `id` lower than this value; must be a positive integer |
| `after_id` | integer | – | Exclusive: only messages with `id` greater than this value; must be a positive integer |
| `sender_id` | integer | – | Only messages sent by this user id |
| `from_date` | string | – | `Y-m-d` or `Y-m-d H:i:s`; lower bound on `created_at` |
| `to_date` | string | – | `Y-m-d` or `Y-m-d H:i:s`; upper bound on `created_at` |

A date-only `to_date` covers the whole day: it is expanded to `23:59:59` of that
day. Date-only `from_date` starts at `00:00:00`. Any other date format is a `400`.

Examples:

```http
GET /api/v1/conversations/225/messages?limit=50&offset=0
GET /api/v1/conversations/225/messages?order=desc&limit=20
GET /api/v1/conversations/225/messages?before_id=1108
GET /api/v1/conversations/225/messages?from_date=2026-08-14&to_date=2026-08-15
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
      "read_at": "2026-08-14 12:00:00",
      "created_at": "2026-08-13 11:00:00",
      "sender_id": 100568
    }
  ],
  "errors": null
}
```

`sender_id` is an alias of `sender_user_id`. Sender email addresses are not
exposed.

### Status codes

| Condition | Status |
| --------- | ------ |
| Invalid query parameter | `400` |
| Not authenticated | `401` |
| Conversation exists but user is not a participant | `403` |
| Conversation does not exist | `404` |
| Success | `200` |

---

# 5. Send a Message

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
  `GET /conversations/{id}` and `GET /conversations/{id}/messages` keep working.
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

Both statements commit together. If either fails, the whole transaction is rolled
back — a message is never stored without the conversation timestamp, and the
timestamp is never advanced without a stored message. Diagnostics go to the
database log; the client only ever receives the generic
`Unable to send message.` with `500`, never SQL or exception details.

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
* Real-time delivery (WebSocket) and push notifications.

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
| `500` | Unexpected server/database failure — safe generic message only |

# MASAR WebSocket — Real-time messaging events

This document describes the **working** WebSocket server shipped with the
backend. It is a real RFC 6455 implementation using PHP core stream functions;
it requires no external message broker, no additional Composer package and no
`ext-sockets`/`pcntl` extension. It runs under the stock Laragon Windows PHP CLI.

The HTTP API remains the source of truth. WebSocket is used only for real-time
notification of messages that have already been committed to MySQL.

---

## Architecture

```
HTTP POST /conversations/{id}/messages
        │
        ├─ db_transaction()
        │     ├─ INSERT messages
        │     ├─ UPDATE conversations.updated_at
        │     └─ INSERT message_events        (transactional outbox)
        │
        └─ HTTP 201 response

WebSocket server (CLI process)
        ├─ authenticates JWT on handshake
        ├─ authorizes conversation subscriptions against MySQL
        ├─ drains message_events every WS_OUTBOX_INTERVAL_MS
        ├─ broadcasts message.created to subscribed participants
        └─ marks events dispatched
```

* **Outbox** — the event row is written in the same transaction as the message,
  so an event can never be broadcast before commit and a failed insert can never
  produce an event.
* **Downtime** — persisted messages are unaffected. Events left pending while the
  server is down are discarded on startup (no stale replay). Clients recover
  missed messages with `GET /conversations/{id}/messages?after_id=<last_seen_id>`.
* **No broker** — the outbox table is the queue; the server polls it internally.
  This is *not* client-side polling: the client keeps a single WebSocket
  connection and receives pushed events.

---

## Files

| File | Purpose |
| ---- | ------- |
| `websocket/server.php` | CLI entry point |
| `app/modules/messaging/websocket/websocket_server.php` | event loop, auth, handshake, outbox relay |
| `app/modules/messaging/websocket/websocket_hub.php` | connection registry and authorized subscription registry |
| `app/modules/messaging/websocket/websocket_protocol.php` | RFC 6455 framing helpers |
| `app/modules/messaging/repositories/message_event_repository.php` | outbox queries |
| `database/migrations/040_create_message_events_outbox.sql` | outbox table |
| `docs/WEBSOCKET.md` | this document |

---

## Configuration

Add these to `.env` (defaults are shown; see `.env.example`):

| Variable | Default | Description |
| -------- | ------- | ----------- |
| `WS_HOST` | `127.0.0.1` | Bind address. Keep loopback and terminate TLS in a proxy. |
| `WS_PORT` | `8081` | Listen port. |
| `WS_MAX_CONNECTIONS` | `100` | Maximum simultaneous connections. |
| `WS_PING_INTERVAL` | `30` | Seconds between server pings. |
| `WS_OUTBOX_INTERVAL_MS` | `500` | Outbox drain interval. |
| `WS_OUTBOX_BATCH` | `50` | Maximum events drained per tick. |

The server uses the same `JWT_SECRET`, `JWT_ALGORITHM`, database and
`APP_TIMEZONE` settings as the HTTP API. No separate secret is introduced.

---

## Startup (Laragon / Windows)

From `backend/`:

```bat
php websocket/server.php
```

The server is CLI-only (`PHP_SAPI === 'cli'`) and cannot be started through
Apache. In production run it under a process supervisor so a crash or a stale
database connection is restarted automatically.

A local reverse proxy can expose it as `wss://…`; no plaintext `ws://` should be
exposed publicly because the browser token is passed in the query string.

---

## Connection URL

```text
ws://127.0.0.1:8081/?token=ACCESS_TOKEN
```

Authentication is accepted in two forms:

1. `Authorization: Bearer ACCESS_TOKEN` handshake header (non-browser clients).
2. `?token=ACCESS_TOKEN` query parameter (browser clients, which cannot set
   custom headers on a WebSocket handshake).

The token is the **existing JWT access token** issued by the HTTP auth API. The
server validates signature, expiry and token type, re-loads the user from MySQL
and rejects inactive users. Tokens are never logged — only connection ids and
user ids appear in logs.

Handshake failures return HTTP errors before upgrade:

| Status | Meaning |
| ------ | ------- |
| `400` | Missing/invalid WebSocket upgrade headers |
| `401` | Missing, invalid or expired token; inactive user |
| `429` | Too many connection attempts from this IP |
| `503` | Server connection limit reached |

---

## Client messages (text frames, JSON)

### Subscribe

```json
{ "type": "subscribe", "conversation_id": 225 }
```

The server **re-checks membership against the database** using the same
authorization helper as the HTTP API. A client cannot subscribe to a conversation
it does not belong to. A baseline message id is captured at subscribe time so
history is never replayed as live events.

Success:

```json
{ "type": "subscribed", "data": { "conversation_id": 225, "baseline_message_id": 1108 } }
```

Failure:

```json
{ "type": "error", "error": "forbidden", "message": "You are not a participant in this conversation." }
```

`error` is `validation_error`, `unauthorized`, `forbidden`, `not_found`,
`unknown_event`, `protocol_error` or `unsupported_frame`.

### Unsubscribe

```json
{ "type": "unsubscribe", "conversation_id": 225 }
```

Ack: `{ "type": "unsubscribed", "data": { "conversation_id": 225 } }`

### Application ping

```json
{ "type": "ping" }
```

Ack: `{ "type": "pong", "data": {} }`

### Sending messages

WebSocket clients **cannot send chat messages**. Message creation happens only
through `POST /api/v1/conversations/{id}/messages`, which enforces all existing
business rules (participant access, application status including withdrawal,
body validation). A `message.send`-style frame is reported as `unknown_event`.

---

## Server events

### `message.created`

Delivered only to authenticated subscribers of that conversation, and only for
messages created after their subscription baseline:

```json
{
  "type": "message.created",
  "data": {
    "event_id": 42,
    "conversation_id": 225,
    "message_id": 1234,
    "sender_user_id": 100532,
    "body": "Hello!",
    "created_at": "2026-11-20 10:15:00"
  }
}
```

The payload intentionally contains no email, token, database metadata or other
private information. `event_id` can be used by clients to deduplicate.

### `message.updated`

Sent after a message is edited via `PATCH /conversations/{id}/messages/{id}`
and the database update commits:

```json
{
  "event": "message.updated",
  "conversation_id": 262,
  "message": {
    "id": 3263,
    "sender_user_id": 100588,
    "body": "Updated message content.",
    "is_edited": true,
    "created_at": "2026-11-20 18:10:03",
    "updated_at": "2026-11-20 21:00:00"
  }
}
```

Notes:
* The message `id` is unchanged; `created_at` is unchanged; `is_edited` is
  `true`.
* Only participants of the conversation receive it. Unauthorized connections
  receive nothing.
* Never published before the edit transaction commits; a failed edit produces
  no event.

### `message.deleted`

Sent after a message is hard-deleted via
`DELETE /conversations/{id}/messages/{id}` and the delete commits:

```json
{
  "event": "message.deleted",
  "conversation_id": 262,
  "message_id": 3263,
  "deleted_by": 100588
}
```

Notes:
* The event deliberately does **not** contain the deleted message body.
* Persisted messages are never re-broadcast — the database is authoritative
  and the outbox relays deletion events after commit only.
* Clients that missed the deletion can re-sync via the HTTP messages API with
  the `after_id` cursor.

Control frames:

* Server `ping` (RFC 6455 op `0x9`) expects a `pong`. Connections that miss
  pongs for three intervals are closed.
* Close frames use code `1000` (normal), `1002` (protocol error) or `1003`
  (unsupported frame type).

---

## Error handling and reconnection

* If the connection drops, reconnect with a fresh handshake and re-subscribe.
* Because delivery is at-most-once with an outbox, clients should treat the last
  seen `message_id` as a recovery cursor:
  `GET /api/v1/conversations/{id}/messages?after_id=<last_seen_id>&limit=20`.
  This closes any gap caused by a disconnect or server restart.
* Do not assume an event will be replayed after downtime — it will not. The
  database is always authoritative.
* The server discards stale outbox rows on startup; persisted messages remain.

---

## Verification

Run the end-to-end suite (it starts the real server):

```bash
php tests/messaging_websocket_regression.php
```

It covers authentication, subscription authorization, delivery to both
participants, non-delivery to third parties, withdrawal enforcement, duplicate
prevention, downtime persistence and `after_id` recovery.

Edit/delete events are covered by:

```bash
php tests/messaging_edit_delete_regression.php
```

which starts the real server, sends a message over HTTP, edits it, deletes it
and asserts `message.updated` / `message.deleted` reach both participants (and
no one else).

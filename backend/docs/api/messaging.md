# MASAR API — Messaging

## Overview

The Messaging API manages private conversations and messages between users on the MASAR platform.

It covers:

* Conversations.
* Conversation participants.
* Sending messages.
* Reading messages.
* Message pagination.
* Unread messages.
* Attachments.
* Conversation state.
* Authorization and privacy.

Base URL:

```text
/api/messaging
```

All messaging endpoints require authentication.

---

# Authentication

Protected endpoints require:

```http
Authorization: Bearer ACCESS_TOKEN
Accept: application/json
```

The authenticated user is identified from the access token.

The client must never provide the authenticated user's ID as the authoritative sender identity.

---

# 1. List My Conversations

Returns conversations accessible to the authenticated user.

### Endpoint

```http
GET /api/messaging/conversations
```

### Query Parameters

```text
page
per_page
```

### Response

```json
{
    "success": true,
    "data": [
        {
            "id": 100,
            "other_user": {
                "id": 25,
                "name": "Ahmed Mohamed"
            },
            "last_message": {
                "id": 500,
                "body": "Hello, I wanted to ask about the training.",
                "sent_at": "2026-08-07 18:30:00"
            },
            "unread_count": 2,
            "updated_at": "2026-08-07 18:30:00"
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

Only conversations in which the authenticated user is a participant may be returned.

---

# 2. Get Conversation

Returns conversation details and participants.

### Endpoint

```http
GET /api/messaging/conversations/{conversationId}
```

### Response

```json
{
    "success": true,
    "data": {
        "id": 100,
        "participants": [
            {
                "id": 4,
                "name": "Example Company"
            },
            {
                "id": 25,
                "name": "Ahmed Mohamed"
            }
        ],
        "created_at": "2026-08-07 17:00:00",
        "updated_at": "2026-08-07 18:30:00"
    }
}
```

The API must verify that the authenticated user belongs to the conversation.

---

# 3. Create Conversation

Creates a new private conversation.

### Endpoint

```http
POST /api/messaging/conversations
```

### Request

```json
{
    "participant_id": 4
}
```

### Response

```json
{
    "success": true,
    "message": "Conversation created successfully.",
    "data": {
        "id": 100,
        "participants": [
            {
                "id": 25,
                "name": "Ahmed Mohamed"
            },
            {
                "id": 4,
                "name": "Example Company"
            }
        ]
    }
}
```

The authenticated user is automatically added as a participant.

The authenticated user's ID must be taken from the authentication context.

---

# 4. Find Existing Conversation

The application may reuse an existing direct conversation instead of creating duplicates.

### Endpoint

```http
GET /api/messaging/conversations/with/{userId}
```

### Response

```json
{
    "success": true,
    "data": {
        "id": 100,
        "participant": {
            "id": 4,
            "name": "Example Company"
        }
    }
}
```

For one-to-one conversations, the system should normally maintain one active direct conversation between the same participants unless the business rules explicitly allow multiple conversations.

---

# 5. List Messages

Returns messages in a conversation.

### Endpoint

```http
GET /api/messaging/conversations/{conversationId}/messages
```

### Query Parameters

```text
page
per_page
before
after
```

### Example

```http
GET /api/messaging/conversations/100/messages?page=1&per_page=50
```

### Response

```json
{
    "success": true,
    "data": [
        {
            "id": 500,
            "sender": {
                "id": 4,
                "name": "Example Company"
            },
            "body": "Hello, I wanted to ask about the training.",
            "sent_at": "2026-08-07 18:30:00",
            "read_at": null
        },
        {
            "id": 501,
            "sender": {
                "id": 25,
                "name": "Ahmed Mohamed"
            },
            "body": "Sure, how can I help?",
            "sent_at": "2026-08-07 18:35:00",
            "read_at": "2026-08-07 18:36:00"
        }
    ],
    "meta": {
        "current_page": 1,
        "per_page": 50,
        "total": 2,
        "last_page": 1
    }
}
```

Messages should normally be returned in chronological order within the requested page.

---

# 6. Send Message

Sends a message to a conversation.

### Endpoint

```http
POST /api/messaging/conversations/{conversationId}/messages
```

### Request

```json
{
    "body": "Hello, I wanted to ask about the training."
}
```

### Response

```json
{
    "success": true,
    "message": "Message sent successfully.",
    "data": {
        "id": 500,
        "conversation_id": 100,
        "sender_id": 4,
        "body": "Hello, I wanted to ask about the training.",
        "sent_at": "2026-08-07 18:30:00"
    }
}
```

The sender must always be taken from the authenticated session.

---

# 7. Send Message With Attachment

Allows a message to reference an uploaded file.

### Endpoint

```http
POST /api/messaging/conversations/{conversationId}/messages
```

### Request

```json
{
    "body": "Please find the document attached.",
    "file_id": 250
}
```

The file must:

* Exist.
* Belong to the authenticated user or be otherwise authorized for use.
* Be an allowed file type.
* Satisfy the configured size limit.

### Response

```json
{
    "success": true,
    "message": "Message sent successfully.",
    "data": {
        "id": 502,
        "body": "Please find the document attached.",
        "file": {
            "id": 250,
            "name": "training-document.pdf"
        },
        "sent_at": "2026-08-07 18:40:00"
    }
}
```

---

# 8. Get Message

Returns a specific message.

### Endpoint

```http
GET /api/messaging/messages/{messageId}
```

### Response

```json
{
    "success": true,
    "data": {
        "id": 500,
        "conversation_id": 100,
        "sender": {
            "id": 4,
            "name": "Example Company"
        },
        "body": "Hello, I wanted to ask about the training.",
        "sent_at": "2026-08-07 18:30:00",
        "read_at": null
    }
}
```

The user must be a participant in the related conversation.

---

# 9. Mark Message as Read

Marks a message as read by the authenticated user.

### Endpoint

```http
POST /api/messaging/messages/{messageId}/read
```

### Response

```json
{
    "success": true,
    "message": "Message marked as read.",
    "data": {
        "message_id": 500,
        "read_at": "2026-08-07 18:45:00"
    }
}
```

The system must not allow one user to manipulate another user's read state.

---

# 10. Mark Conversation as Read

Marks eligible unread messages in a conversation as read.

### Endpoint

```http
POST /api/messaging/conversations/{conversationId}/read
```

### Response

```json
{
    "success": true,
    "message": "Conversation marked as read."
}
```

Only messages received by the authenticated user should be affected.

---

# 11. Get Unread Message Count

Returns the number of unread messages for the authenticated user.

### Endpoint

```http
GET /api/messaging/unread-count
```

### Response

```json
{
    "success": true,
    "data": {
        "unread_count": 7
    }
}
```

---

# 12. Delete Message

Deletes or hides a message according to the application's message-retention policy.

### Endpoint

```http
DELETE /api/messaging/messages/{messageId}
```

### Response

```json
{
    "success": true,
    "message": "Message deleted successfully."
}
```

If the system uses soft deletion, the underlying record should remain available for audit and integrity purposes.

A deleted message may be represented as:

```json
{
    "id": 500,
    "body": null,
    "deleted_at": "2026-08-07 19:00:00"
}
```

---

# 13. Search Conversations

Searches conversations available to the authenticated user.

### Endpoint

```http
GET /api/messaging/conversations/search
```

### Query Parameters

```text
q
page
per_page
```

### Example

```http
GET /api/messaging/conversations/search?q=Ahmed&page=1&per_page=20
```

### Response

```json
{
    "success": true,
    "data": [
        {
            "id": 100,
            "participant": {
                "id": 25,
                "name": "Ahmed Mohamed"
            }
        }
    ]
}
```

Search must be restricted to conversations accessible to the authenticated user.

---

# 14. Archive Conversation

Archives a conversation for the authenticated user.

### Endpoint

```http
POST /api/messaging/conversations/{conversationId}/archive
```

### Response

```json
{
    "success": true,
    "message": "Conversation archived successfully."
}
```

Archiving should normally affect the user's view rather than deleting the conversation for all participants.

---

# 15. Unarchive Conversation

Restores an archived conversation to the user's active conversation list.

### Endpoint

```http
POST /api/messaging/conversations/{conversationId}/unarchive
```

### Response

```json
{
    "success": true,
    "message": "Conversation restored successfully."
}
```

---

# Conversation Rules

A conversation must contain valid participants.

For a direct conversation:

```text
User A
   ↕
User B
```

The system should prevent invalid states such as:

```text
No participants
Duplicate participant
Unauthorized participant
```

---

# Message Rules

A message must belong to an existing conversation.

The sender must:

1. Be authenticated.
2. Be a participant in the conversation.
3. Have permission to send messages.
4. Provide valid message content or an authorized attachment.

---

# Empty Messages

A message should not be accepted when both content and attachment are absent.

Invalid example:

```json
{
    "body": ""
}
```

The API should return:

```json
{
    "success": false,
    "message": "Message content or attachment is required."
}
```

---

# Message Length

The API should enforce a maximum message length defined by the application.

Example:

```text
MAX_MESSAGE_LENGTH
```

The exact limit should be centralized in the application's validation/configuration layer.

---

# Attachments

Message attachments must be processed through the Files API.

Recommended flow:

```text
Upload file
    ↓
Create files record
    ↓
Validate ownership
    ↓
Attach file to message
```

The messaging API must not trust a client-provided file path.

---

# Read State

Messages should maintain a read state for each recipient where required.

For a one-to-one conversation:

```text
Message
   ↓
recipient reads message
   ↓
read_at = timestamp
```

For group conversations, read state should be modeled per participant rather than storing only one global `read_at`.

---

# Unread Count

Unread counts should be calculated based on messages:

```text
received by current user
        +
not yet marked as read
```

The sender's own messages should not increase the sender's unread count.

---

# Conversation Privacy

A user must never be able to access a conversation simply by changing:

```text
conversation_id
```

The server must always verify:

```text
conversation.participants
        contains
authenticated_user
```

before returning messages or conversation data.

---

# Authorization

## Student

A student may:

```text
Create conversations where permitted
View own conversations
Send messages in own conversations
Read own messages
Archive own conversations
```

## Company

A company may:

```text
Create conversations where permitted
View own conversations
Send messages in own conversations
Read own messages
Archive own conversations
```

## Administrator

Administrators may have elevated access according to the admin policy.

Administrative access to private messages should be explicitly permission-controlled and audit logged.

---

# Message Notifications

A new message may generate a notification.

Typical flow:

```text
User A
   │
   │ sends message
   ▼
Message created
   │
   ▼
Notification created
   │
   ▼
User B
```

Notification payload should contain enough information to identify the conversation without exposing unnecessary private content.

---

# Audit Logging

Important messaging actions may be recorded:

```text
conversation.created
conversation.archived
message.created
message.deleted
message.read
```

Administrative access to private conversations should always be audit logged.

---

# Pagination

Conversation and message collections support:

```text
page
per_page
```

Example:

```http
GET /api/messaging/conversations?page=1&per_page=20
```

The API should enforce a maximum `per_page`.

For large message histories, cursor-based pagination may be preferred.

Example:

```text
before=2026-08-07T18:30:00Z
```

---

# Standard Errors

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
    "message": "You are not a participant in this conversation."
}
```

## 404 Not Found

```json
{
    "success": false,
    "message": "Conversation or message not found."
}
```

## 409 Conflict

```json
{
    "success": false,
    "message": "Conversation already exists."
}
```

## 422 Validation Error

```json
{
    "success": false,
    "message": "Validation failed.",
    "errors": {}
}
```

---

# Related Database Tables

The Messaging API primarily interacts with:

```text
users
conversations
messages
files
notifications
audit_logs
```

Conversation table:

```text
database/migrations/018_create_conversations_table.sql
```

Messages table:

```text
database/migrations/019_create_messages_table.sql
```

---

# Related Routes

```text
GET    /api/messaging/conversations
POST   /api/messaging/conversations

GET    /api/messaging/conversations/{conversationId}
GET    /api/messaging/conversations/with/{userId}

GET    /api/messaging/conversations/{conversationId}/messages
POST   /api/messaging/conversations/{conversationId}/messages

GET    /api/messaging/messages/{messageId}
DELETE /api/messaging/messages/{messageId}

POST   /api/messaging/messages/{messageId}/read
POST   /api/messaging/conversations/{conversationId}/read

GET    /api/messaging/unread-count

GET    /api/messaging/conversations/search

POST   /api/messaging/conversations/{conversationId}/archive
POST   /api/messaging/conversations/{conversationId}/unarchive

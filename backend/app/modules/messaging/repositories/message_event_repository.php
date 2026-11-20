<?php

/**
 * MASAR - Message Event Repository
 *
 * Transactional outbox for real-time messaging events. All database access
 * goes through the shared query helpers so writes join the caller's
 * transaction and reads use prepared statements.
 */

require_once __DIR__ . '/../../../core/database/query.php';


/*
|--------------------------------------------------------------------------
| Create Event (inside the message transaction)
|--------------------------------------------------------------------------
*/

function message_event_repository_create(
    array $event
): void {

    $event_type =
        trim(
            (string) ($event['event_type'] ?? 'message.created')
        ) ?: 'message.created';


    db_execute(
        'INSERT INTO message_events
            (message_id, conversation_id, sender_user_id, body, created_at, event_type)
         VALUES
            (:message_id, :conversation_id, :sender_user_id, :body, :created_at, :event_type)',
        [
            ':message_id'      => (int) ($event['message_id'] ?? 0),
            ':conversation_id' => (int) ($event['conversation_id'] ?? 0),
            ':sender_user_id'  => (int) ($event['sender_user_id'] ?? 0),
            ':body'            => (string) ($event['body'] ?? ''),
            ':created_at'      => (string) ($event['created_at'] ?? ''),
            ':event_type'      => $event_type,
        ]
    );
}


/*
|--------------------------------------------------------------------------
| Pending Events
|--------------------------------------------------------------------------
*/

function message_event_repository_pending(
    int $limit = 50
): array {

    $limit =
        max(1, min($limit, 200));

    return db_fetch_all(
        'SELECT
            id,
            message_id,
            conversation_id,
            sender_user_id,
            body,
            created_at,
            event_type
         FROM message_events
         WHERE dispatched_at IS NULL
         ORDER BY id ASC
         LIMIT ' . $limit
    );
}


/*
|--------------------------------------------------------------------------
| Mark Dispatched
|--------------------------------------------------------------------------
*/

function message_event_repository_mark_dispatched(
    array $ids
): void {

    $ids =
        array_values(
            array_unique(
                array_map('intval', $ids)
            )
        );

    if ($ids === []) {
        return;
    }

    $placeholders =
        implode(', ', array_fill(0, count($ids), '?'));

    db_execute(
        'UPDATE message_events
         SET dispatched_at = NOW()
         WHERE id IN (' . $placeholders . ')
           AND dispatched_at IS NULL',
        $ids
    );
}


/*
|--------------------------------------------------------------------------
| Discard Stale Backlog
|--------------------------------------------------------------------------
|
| Called once when the WebSocket server starts. Events left pending during
| downtime are intentionally not replayed: HTTP clients recover missed
| messages through the after_id cursor, and this prevents old messages from
| being broadcast again. Persisted messages are never touched.
|
*/

function message_event_repository_discard_pending(): int
{
    $statement =
        db_execute(
            'UPDATE message_events
             SET dispatched_at = NOW()
             WHERE dispatched_at IS NULL'
        );

    return $statement->rowCount();
}


/*
|--------------------------------------------------------------------------
| Latest Message Id
|--------------------------------------------------------------------------
|
| Used to establish a subscription baseline so messages that already existed
| before a client subscribed are never pushed to that client.
|
*/

function message_event_repository_latest_message_id(
    int $conversation_id
): int {

    if ($conversation_id <= 0) {
        return 0;
    }

    return (int) db_fetch_column(
        'SELECT COALESCE(MAX(id), 0) FROM messages WHERE conversation_id = ?',
        [$conversation_id]
    );
}

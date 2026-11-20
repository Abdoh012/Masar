-- ============================================================================
-- Migration 040: Transactional outbox for real-time message.created events
-- ============================================================================
-- A row is inserted in the SAME transaction as the message insert, so the
-- WebSocket server can never broadcast a message that failed to persist.
-- The WebSocket server marks rows dispatched after delivery; rows left behind
-- during downtime are discarded at startup, and clients recover via after_id.
-- ============================================================================

CREATE TABLE IF NOT EXISTS `message_events` (
    `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
    `message_id` bigint UNSIGNED NOT NULL,
    `conversation_id` bigint UNSIGNED NOT NULL,
    `sender_user_id` bigint UNSIGNED NOT NULL,
    `body` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    `created_at` datetime NOT NULL,
    `dispatched_at` datetime DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_message_events_message` (`message_id`),
    KEY `idx_message_events_pending` (`dispatched_at`, `id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

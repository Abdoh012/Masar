-- ============================================================================
-- Migration 039: Messaging performance indexes
-- ============================================================================
-- Supports cursor pagination (conversation_id, id) and unread counts
-- (conversation_id, read_at, sender_user_id). Existing indexes are retained.
-- ============================================================================

ALTER TABLE `messages`
    ADD KEY `idx_messages_conversation_id` (`conversation_id`, `id`),
    ADD KEY `idx_messages_unread` (`conversation_id`, `read_at`, `sender_user_id`);

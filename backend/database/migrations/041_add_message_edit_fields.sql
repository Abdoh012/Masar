-- ============================================================================
-- Migration 041: Message edit metadata + event_type on the outbox
-- ============================================================================
--
-- messages.is_edited / updated_at
--   marks a message as edited; updated_at records the last modification.
--
-- message_events.event_type
--   distinguishes message.created / message.updated / message.deleted.
--   The unique key on message_id is dropped: a single message may produce
--   multiple outbox events (created, then updated, then deleted) over its
--   lifetime. The relayed relay still marks events dispatched in id order.
-- ============================================================================

ALTER TABLE `messages`
  ADD COLUMN `is_edited` TINYINT(1) NOT NULL DEFAULT 0 AFTER `is_read`,
  ADD COLUMN `updated_at` DATETIME NULL AFTER `created_at`;

ALTER TABLE `message_events`
  DROP KEY `uq_message_events_message`;

-- 033: Make payments.training_session_id nullable.
--
-- The `payments` table is reused as the storage for the manual (bank
-- transfer) payment confirmation flow of accepted paid trainings.
--
-- The table requires a `training_session_id`, but in the accepted-application
-- flow there is no guarantee a training session has been created yet (sessions
-- are scheduled later by the company after the student joins). A company must
-- be able to confirm the manual payment as soon as the transfer arrives, even
-- before any session row exists.
--
-- Making the column NULLable keeps the existing session-linked payment rows
-- (demo data seeds one payment per training session) fully intact while
-- allowing application-level confirmations without a session. NULL means "the
-- payment was confirmed for the application/training, not for one scheduled
-- session".

-- 1) Guard: confirm the current definition (informational).
SELECT
    COLUMN_NAME,
    IS_NULLABLE,
    DATA_TYPE
FROM information_schema.COLUMNS
WHERE
    TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'payments'
    AND COLUMN_NAME = 'training_session_id';

-- 2) Relax the constraint.
ALTER TABLE `payments`
    MODIFY COLUMN `training_session_id` bigint UNSIGNED NULL DEFAULT NULL;
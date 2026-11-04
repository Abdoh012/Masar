-- 035: Add the Student Profile fields that have no existing column.
--
-- The Student Profile UI needs four values that the schema cannot express
-- today. Each one is added as the SMALLEST representation that fits the
-- existing model rather than as a new table:
--
--   users.username                     The public handle shown next to a
--                                       student's name. It is deliberately a
--                                       separate column from `email`: the
--                                       product states the handle is NOT the
--                                       email, and it must stay stable when
--                                       the account email changes. Nullable
--                                       so every existing account keeps
--                                       working without a backfill, and
--                                       UNIQUE so a handle identifies one
--                                       account. The table's collation is
--                                       utf8mb4_0900_ai_ci (case- and
--                                       accent-insensitive), so the UNIQUE
--                                       index already rejects `Nour` and
--                                       `nour` as duplicates.
--
--   students.academic_status           The profile-level Student / Graduate
--                                       state. This is NOT the same thing as
--                                       training_applications.applicant_type
--                                       (enum 'student','graduated'), which is
--                                       a per-application self-declaration and
--                                       is left untouched. Training
--                                       eligibility never reads any students
--                                       column, so this column is inert for
--                                       the Applications flow.
--
--   students.graduation_month          The month a degree or certificate was
--                                       awarded (1-12). Pairs with the
--                                       pre-existing `graduation_year`
--                                       (year) column instead of introducing a
--                                       second date column that would compete
--                                       with it. NULL is allowed because a
--                                       current student has not graduated.
--
--   students.academic_certificate_file_id
--                                       A student's OWN academic certificate
--                                       document, reusing the existing `files`
--                                       table and the existing upload/storage
--                                       service. This is unrelated to the
--                                       MASAR-issued `certificates` table,
--                                       which requires a training and a
--                                       completed session and is left
--                                       untouched. Indexed like the existing
--                                       profile_image_file_id / cv_file_id
--                                       columns; deliberately a KEY and not a
--                                       FOREIGN KEY, matching those two
--                                       existing columns.
--
-- Backward compatibility: every addition is nullable or has a default, so no
-- existing row changes meaning and no application record is touched. The
-- `username` backfill is intentionally NOT performed: a handle must be chosen
-- by its owner, and inventing one from the email local-part would re-expose
-- the email the product explicitly says the handle replaces.

-- 1) Guard: confirm the columns do not already exist (informational).
SELECT
    TABLE_NAME,
    COLUMN_NAME
FROM information_schema.COLUMNS
WHERE
    TABLE_SCHEMA = DATABASE()
    AND (
        (TABLE_NAME = 'users' AND COLUMN_NAME = 'username')
        OR (TABLE_NAME = 'students' AND COLUMN_NAME IN ('academic_status', 'graduation_month', 'academic_certificate_file_id'))
    );

-- 2) The public username handle on the account.
ALTER TABLE `users`
    ADD COLUMN `username` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `email`,
    ADD UNIQUE KEY `uq_users_username` (`username`);

-- 3) The profile-level academic status.
ALTER TABLE `students`
    ADD COLUMN `academic_status` enum('student','graduate') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'student' AFTER `graduation_year`;

-- 4) The month a degree / certificate was awarded (pairs with graduation_year).
ALTER TABLE `students`
    ADD COLUMN `graduation_month` tinyint UNSIGNED DEFAULT NULL AFTER `graduation_year`;

-- 5) The student's own academic certificate document (reuses the files table).
ALTER TABLE `students`
    ADD COLUMN `academic_certificate_file_id` bigint UNSIGNED DEFAULT NULL AFTER `cv_file_id`,
    ADD KEY `idx_students_academic_certificate_file` (`academic_certificate_file_id`);

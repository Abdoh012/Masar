-- 037: Add the three Student Contact fields that have no existing column.
--
-- Email lives on users.email and phone on students.phone. The contact endpoint
-- also supports a personal website / portfolio URL, an external professional
-- profile URL (for example LinkedIn), and a free-text address. None of these
-- has storage today: companies.website / companies.address belong to the
-- company profile and training_applications.address belongs to an application,
-- so reusing either would mix unrelated data.
--
-- All three are nullable: they are optional and every existing student keeps
-- working without a backfill. website / external_profile are capped at 255 like
-- companies.website; address is capped at 500 like companies.address and
-- training_applications.address.

SELECT
    TABLE_NAME,
    COLUMN_NAME
FROM information_schema.COLUMNS
WHERE
    TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'students'
    AND COLUMN_NAME IN ('website', 'external_profile', 'address');

ALTER TABLE `students`
    ADD COLUMN `website` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `phone`;

ALTER TABLE `students`
    ADD COLUMN `external_profile` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `website`;

ALTER TABLE `students`
    ADD COLUMN `address` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `city`;

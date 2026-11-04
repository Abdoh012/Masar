-- 038: Add the graduation_date column used by the Academic Status feature.
--
-- The Academic Status feature now stores a full graduation date (YYYY-MM-DD)
-- instead of only a month/year. The legacy graduation_year / graduation_month
-- columns are kept for backward compatibility and are still populated from the
-- date. The standalone Academic Certificate feature has been removed; its
-- file reference column (academic_certificate_file_id) is now used exclusively
-- by the Academic Status graduation certificate.

SELECT
    TABLE_NAME,
    COLUMN_NAME
FROM information_schema.COLUMNS
WHERE
    TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'students'
    AND COLUMN_NAME = 'graduation_date';

ALTER TABLE `students`
    ADD COLUMN `graduation_date` date DEFAULT NULL AFTER `graduation_month`;

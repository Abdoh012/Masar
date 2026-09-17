-- 034: Widen certificates.grade from decimal(5,2) to varchar(10).
--
-- The grade column was originally defined as a numeric percentage (decimal(5,2)),
-- but the product uses human-readable letter grades (A+, A, B+, B, C+, C).
-- Widening the column allows storing letter grades while retaining backward
-- compatibility with any existing numeric grade values (stored as strings).
--
-- Minimal column-type change: no new columns, no column renames.

-- 1) Guard: confirm the current definition (informational).
SELECT
    COLUMN_NAME,
    IS_NULLABLE,
    DATA_TYPE,
    COLUMN_TYPE
FROM information_schema.COLUMNS
WHERE
    TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'certificates'
    AND COLUMN_NAME = 'grade';

-- 2) Widen the column.
ALTER TABLE `certificates`
    MODIFY COLUMN `grade` varchar(10) NULL DEFAULT NULL;

-- 030: Convert training_applications university from ID reference to free text.
--
-- The student previously submitted `university_id` (a reference into the
-- `universities` lookup table). It is now entered as a plain text value, e.g.
-- "Cairo University" or "Alexandria University", stored in a new `university`
-- varchar(255) column. The client-facing Create Application API therefore uses
-- `university` (text) instead of `university_id`.
--
-- Data handling: existing `training_applications.university_id` values are NOT
-- discarded. Every stored id references a row in `universities`, so they are
-- resolved to the actual university name (universities.name) and written into
-- the new `university` column before the old `university_id` column and its
-- foreign key are dropped. This preserves all existing application records
-- without inventing names.
--
-- Faculties are intentionally left unchanged: `faculties.university_id` and
-- `training_applications.faculty_id` remain ID-based per existing design.

-- 1) Drop the foreign key that tied university_id to universities.id.
ALTER TABLE `training_applications`
    DROP FOREIGN KEY `fk_applications_university`;

-- 2) Add the free-text university column (varchar length matches
--    universities.name and other name columns across the schema).
ALTER TABLE `training_applications`
    ADD COLUMN `university` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `faculty_id`;

-- 3) Backfill existing values with the real university name.
--    university_id -> universities.id -> universities.name.
UPDATE training_applications ta
    LEFT JOIN universities u ON u.id = ta.university_id
    SET ta.university = u.name
    WHERE ta.university_id IS NOT NULL
      AND u.id IS NOT NULL;

-- 4) Remove the now-unused university_id column and its index.
ALTER TABLE `training_applications`
    DROP COLUMN `university_id`;

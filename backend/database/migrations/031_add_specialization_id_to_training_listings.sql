-- 031: Add specialization_id to training_listings.
--
-- Assigning a single primary specialization to every training listing so the
-- training discovery pipeline (list, search, filter, saved) can scope trainings
-- directly by `training_listings.specialization_id` instead of joining the
-- `training_specializations` pivot for every comparison.
--
-- Company Create Training is switched to require `specialization_id`; the
-- pivot inheritance from company_specializations stays intact for the company
-- view (company/admin unaffected: they still read `training_specializations`).
--
-- Data handling: existing rows are backfilled from the training_specializations
-- pivot. Today every training has exactly one pivot row, so the backfill picks
-- the single specialization. For safety the SELECT guards below flag any
-- training that has zero or multiple pivot rows BEFORE the column is added, so
-- an unexpected data shape can be reviewed instead of silently producing a NULL
-- or an arbitrary value. The UPDATE itself is deterministic: MIN(id) is used,
-- which is a single unambiguous value regardless of pivot count.

-- 1) Guard: list trainings that have no pivot row or more than one pivot row.
SELECT t.id AS training_id, COUNT(ts.specialization_id) AS pivot_count
FROM training_listings t
LEFT JOIN training_specializations ts ON ts.training_id = t.id
GROUP BY t.id
HAVING COUNT(ts.specialization_id) <> 1;

-- 2) Add the nullable column (NOT NULL applied in step 6, after backfill).
ALTER TABLE `training_listings`
    ADD COLUMN `specialization_id` bigint UNSIGNED DEFAULT NULL AFTER `company_id`,
    ADD KEY `idx_training_listings_specialization` (`specialization_id`);

-- 3) Backfill from the pivot. MIN(specialization_id) is deterministic and is
--    the single specialization for every currently correctly-shaped training.
UPDATE training_listings t
    LEFT JOIN (
        SELECT training_id, MIN(specialization_id) AS specialization_id
        FROM training_specializations
        GROUP BY training_id
    ) ts ON ts.training_id = t.id
    SET t.specialization_id = ts.specialization_id
    WHERE t.specialization_id IS NULL;

-- 4) Guard: rows that could not be backfilled (would break the NOT NULL step).
SELECT id, title, specialization_id
FROM training_listings
WHERE specialization_id IS NULL;

-- 5) Add the foreign key (RESTRICT: a specialization cannot be deleted while
--    a training listing still references it).
ALTER TABLE `training_listings`
    ADD CONSTRAINT `fk_training_listings_specialization`
    FOREIGN KEY (`specialization_id`) REFERENCES `specializations` (`id`)
    ON DELETE RESTRICT;

-- 6) Enforce the invariant going forward: every training has one primary
--    specialization.
ALTER TABLE `training_listings`
    MODIFY COLUMN `specialization_id` bigint UNSIGNED NOT NULL AFTER `company_id`;
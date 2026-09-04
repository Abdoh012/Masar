-- 027: Remove the obsolete `field` column from training_listings.
--
-- The training's specialization source of truth is now exclusively:
--
--   training_listings.company_id
--       -> company_specializations.specialization_id
--       -> training_specializations.training_id + specialization_id
--
-- inherited automatically when a training is created/updated.
-- This migration only removes the redundant column and its index.
-- It does NOT touch training_specializations, company_specializations,
-- company_work_fields, or any training rows.

ALTER TABLE `training_listings`
    DROP INDEX `idx_training_field`;

ALTER TABLE `training_listings`
    DROP COLUMN `field`;

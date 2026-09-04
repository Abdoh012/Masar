-- MASAR Migration 026
-- Add field_id to specializations.
--
-- Establishes the FIELD -> SPECIALIZATION relationship:
-- a specialization belongs to exactly one study field. Student
-- registration validates that the chosen specialization belongs to the
-- chosen field, and the company industry resolves against the same
-- specializations table (company_specializations.specialization_id).
--
-- Nullable + ON DELETE SET NULL so legacy rows without a field keep working.

ALTER TABLE `specializations`
    ADD COLUMN `field_id` bigint UNSIGNED DEFAULT NULL AFTER `parent_id`,
    ADD KEY `idx_specializations_field` (`field_id`),
    ADD CONSTRAINT `fk_specializations_field`
        FOREIGN KEY (`field_id`) REFERENCES `study_fields` (`id`)
        ON DELETE SET NULL;

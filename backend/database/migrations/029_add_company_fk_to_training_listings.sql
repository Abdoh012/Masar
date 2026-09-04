-- 029: Add foreign key from training_listings.company_id to companies.id
--       with ON DELETE CASCADE.
--
-- Ensures that when a company is permanently deleted, all of its training
-- listings are automatically removed. This prevents orphaned training_listings
-- rows from accumulating after company deletion.
--
-- Safety: orphaned training_listings (company_id pointing to a non-existent
-- company) are deleted before the constraint is added to avoid ALTER TABLE
-- failure. This is a one-time cleanup; future deletes use the cascade.
--
-- IMPORTANT: tables that reference training_listings.id (certificates,
-- payments) use ON DELETE RESTRICT, so a company deletion that would also
-- remove training_listings with existing certificates or payments will be
-- blocked at the database level. This is the intended safe behavior.

-- Remove orphaned training_listings whose company no longer exists.
DELETE FROM training_listings
WHERE company_id NOT IN (SELECT id FROM companies);

-- Drop the plain index on company_id (will be replaced by the FK index).
ALTER TABLE `training_listings`
    DROP INDEX `idx_training_company`;

-- Add the foreign key with ON DELETE CASCADE.
ALTER TABLE `training_listings`
    ADD CONSTRAINT `fk_training_listings_company`
    FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`)
    ON DELETE CASCADE;

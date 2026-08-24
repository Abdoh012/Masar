-- 028: Add company_logo to companies.
--
-- Stores the relative public storage path of the company logo image
-- (same convention as the file upload service's relative paths), e.g.:
--
--     companies/20260821_a1b2c3d4e5f6.png
--
-- The binary image itself lives in the upload storage directory and is
-- managed by the existing files module. training_listings does NOT get
-- a company_logo column: trainings resolve their company's current logo
-- dynamically through training_listings.company_id -> companies.id.

ALTER TABLE `companies`
    ADD COLUMN `company_logo` VARCHAR(500) NULL DEFAULT NULL AFTER `city`;

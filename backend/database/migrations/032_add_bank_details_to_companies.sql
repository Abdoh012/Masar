-- 032: Add bank details to companies.
--
-- Companies that run PAID trainings need to publish authorized bank-transfer
-- details to students whose accepted applications require a manual payment.
-- There is no payment gateway in the product: students pay by bank transfer
-- and the company confirms receipt manually.
--
-- The fields are stored on the COMPANY (not on each training) because the
-- transfer destination is a property of the company's own account, it is the
-- same for every paid training the company runs, and keeping it in one place
-- prevents the same bank account from drifting across many training rows.
--
-- All four columns are NULLable: a company only fills them in if it actually
-- runs paid trainings. They are sensitive and must never be selected on the
-- public paths (training list / search / saved / company public profile).
-- Exposed ONLY via:
--   - PUT /api/v1/companies/me  (owner writes them)
--   - GET  /api/v1/companies/me  (owner reads them back)
--   - GET  /api/v1/applications/accepted  (accepted student reads them)
--
-- The values are demo/placeholder-safe; real accounts are entered by the
-- owning company through its own profile.

-- 1) Guard: confirm the columns do not exist yet (informational).
SELECT
    COLUMN_NAME
FROM information_schema.COLUMNS
WHERE
    TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'companies'
    AND COLUMN_NAME IN ('bank_name', 'bank_account_name', 'bank_account_number', 'bank_transfer_instructions');

-- 2) Add the nullable columns (kept NULLable on purpose: free-only companies
--    legitimately never set them; a NOT NULL default would fabricate fake
--    banking data).
ALTER TABLE `companies`
    ADD COLUMN `bank_name` varchar(255) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL AFTER `address`,
    ADD COLUMN `bank_account_name` varchar(255) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL AFTER `bank_name`,
    ADD COLUMN `bank_account_number` varchar(100) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL AFTER `bank_account_name`,
    ADD COLUMN `bank_transfer_instructions` text COLLATE utf8mb4_unicode_ci NULL AFTER `bank_account_number`;
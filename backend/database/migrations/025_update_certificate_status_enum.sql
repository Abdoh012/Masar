-- Update certificate status enum to support full MVP status transition matrix
-- Previous enum: enum('requested','approved','rejected','revoked')
-- New enum: enum('pending','issued','active','valid','revoked','expired')

ALTER TABLE certificates
MODIFY COLUMN status ENUM('pending', 'issued', 'active', 'valid', 'revoked', 'expired') NOT NULL;

-- Map existing values to new enum where applicable
-- 'requested' -> 'pending'
-- 'approved' -> 'issued'
-- 'rejected' -> 'revoked'
-- 'revoked' stays as 'revoked'

UPDATE certificates
SET status = 'pending'
WHERE status = 'requested';

UPDATE certificates
SET status = 'issued'
WHERE status = 'approved';

-- Note: 'revoked' stays as 'revoked' (already matches new enum)
-- 'pending', 'issued', 'active', 'valid' are new and will default appropriately
-- 'expired' is new and will be applied through business logic
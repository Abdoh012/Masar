-- oauth_states
-- One-time OAuth state (CSRF) storage for the Google OAuth flow.
-- Only the SHA-256 nonce of the randomly-generated state is stored, never the raw state.
--   nonce      = SHA-256 hex of the state value (indexed, UNIQUE)
--   expires_at = expiry timestamp; states are rejected after this time
--   used_at    = one-time consumption marker (NULL until the state is validated on callback)
CREATE TABLE IF NOT EXISTS oauth_states (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nonce VARCHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    used_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_oauth_states_nonce (nonce),
    INDEX idx_oauth_states_expires (expires_at)
) ENGINE=InnoDB;
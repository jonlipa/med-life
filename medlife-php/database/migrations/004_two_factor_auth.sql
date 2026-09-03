ALTER TABLE users ADD COLUMN IF NOT EXISTS two_factor_secret VARCHAR(64) NULL DEFAULT NULL AFTER password_hash;
ALTER TABLE users ADD COLUMN IF NOT EXISTS two_factor_enabled TINYINT(1) NOT NULL DEFAULT 0 AFTER two_factor_secret;
ALTER TABLE users ADD COLUMN IF NOT EXISTS two_factor_confirmed_at DATETIME NULL DEFAULT NULL AFTER two_factor_enabled;

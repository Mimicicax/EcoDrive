CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(250) NOT NULL UNIQUE,
    password VARCHAR(250) NOT NULL,
    salt CHAR(16) NOT NULL,
    reset_token CHAR(44) NULL DEFAULT NULL,
    reset_token_expiry TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX IF NOT EXISTS idx_users_email ON users(email);

CREATE INDEX IF NOT EXISTS idx_users_reset_token ON users(reset_token);

ALTER TABLE users 
ADD CONSTRAINT IF NOT EXISTS chk_email_format 
CHECK (email REGEXP '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\\.[A-Za-z]{2,}$');

ALTER TABLE users 
ADD CONSTRAINT IF NOT EXISTS chk_username_format 
CHECK (username REGEXP '^[A-Za-z0-9_-]{3,50}$');

ALTER TABLE users 
ADD CONSTRAINT IF NOT EXISTS chk_password_format 
CHECK (password REGEXP '^[0-9]+@[a-f0-9]+$');

ALTER TABLE users 
ADD CONSTRAINT IF NOT EXISTS chk_salt_length 
CHECK (CHAR_LENGTH(salt) = 16);

ALTER TABLE users 
ADD CONSTRAINT IF NOT EXISTS chk_reset_token_length 
CHECK (reset_token IS NULL OR CHAR_LENGTH(reset_token) = 44);

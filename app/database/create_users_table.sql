CREATE DATABASE IF NOT EXISTS ecodrive_db;
USE ecodrive_db;

CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(250) NOT NULL UNIQUE,
    password VARCHAR(250) NOT NULL,
    salt CHAR(16) NOT NULL,
    reset_token CHAR(44) NULL DEFAULT NULL,
    reset_token_expiry TIMESTAMP NULL DEFAULT NULL,
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX IF NOT EXISTS idx_users_email ON users(email);
CREATE INDEX IF NOT EXISTS idx_users_reset_token ON users(reset_token);

--INSERT INTO users (username, email, password, salt) 
--SELECT 'admin', 'admin@ecodrive.com', '1@e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855', 'abcdef1234567890'
--WHERE NOT EXISTS (
    --SELECT 1 FROM users WHERE username = 'admin' OR email = 'admin@ecodrive.com'
--);

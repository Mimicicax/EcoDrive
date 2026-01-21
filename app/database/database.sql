CREATE USER IF NOT EXISTS 'ecodrive'@'127.0.0.1' IDENTIFIED BY 'ecodrive2026';

DROP DATABASE IF EXISTS ecodrive;
CREATE DATABASE ecodrive DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
USE ecodrive;

GRANT ALL PRIVILEGES ON `ecodrive`.* TO 'ecodrive'@'127.0.0.1' IDENTIFIED BY 'ecodrive2026';

CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(250) NOT NULL UNIQUE,
    password VARCHAR(250) NOT NULL,
    reset_token CHAR(44) NULL DEFAULT NULL,
    reset_token_expiry TIMESTAMP NULL DEFAULT NULL,
    is_admin BOOLEAN DEFAULT FALSE
);

CREATE TABLE IF NOT EXISTS sessions (
    id INTEGER PRIMARY KEY AUTO_INCREMENT,
    user INTEGER NOT NULL UNIQUE,
    session_id CHAR(44) NOT NULL UNIQUE, 
    expiry TIMESTAMP NOT NULL,

    FOREIGN KEY(user) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS vehicles (
    id INT UNSIGNED AUTO_INCREMENT, 
    user INT NOT NULL, 
    brand VARCHAR(20) NOT NULL,
    model VARCHAR(20) NOT NULL,
    license_plate VARCHAR(10) UNIQUE NOT NULL,
    year INT NOT NULL,
    consumption FLOAT NOT NULL,
    emission FLOAT NOT NULL,
    PRIMARY KEY(id),
    FOREIGN KEY(user) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS routes (id INT UNSIGNED AUTO_INCREMENT, 
    vehicle INT UNSIGNED NOT NULL, 
    from_zip SMALLINT UNSIGNED NOT NULL,
    from_city VARCHAR(40) NOT NULL,
    from_street VARCHAR(256) NOT NULL,
    to_zip SMALLINT UNSIGNED NOT NULL,
    to_city VARCHAR(40) NOT NULL,
    to_street VARCHAR(256) NOT NULL,
    travel_start_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP() NOT NULL, 
    distance FLOAT NOT NULL, 
    emission FLOAT DEFAULT 0.0, 
    PRIMARY KEY(id), 
    FOREIGN KEY(vehicle) REFERENCES vehicles(id)
);

SET GLOBAL event_scheduler = ON;

CREATE EVENT IF NOT EXISTS SessionCleanupEvent
ON SCHEDULE EVERY 1 DAY
COMMENT "Kitörli a lejárt sessionöket"
DO DELETE FROM sessions WHERE expiry <= NOW();

-- Adminisztrátor: admin:admin
INSERT INTO users (username, email, password, is_admin)
VALUES("admin", 
       "admin@ecodrive.com", 
       "$argon2i$v=19$m=65536,t=4,p=1$SkN3ekpPZ2N0d3p2UTlCUA$cWDEOQ8MTaYXvdg6ew/gKUUIS8zgeAD7P2yv1oLO1LI",
       1
);

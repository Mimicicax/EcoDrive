-- Átdolgozott séma: tisztább típusok, indexek és karbantartó események
CREATE USER IF NOT EXISTS 'ecodrive'@'localhost' IDENTIFIED BY 'ecodrive2026';

CREATE DATABASE IF NOT EXISTS ecodrive;
CREATE DATABASE ecodrive DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
USE ecodrive;

GRANT ALL ON `ecodrive`.* TO 'ecodrive'@'localhost' IDENTIFIED BY 'ecodrive2026';

-- Users tábla: indexek hozzáadása a username/email mezőkre és nagyobb jelszó-tárolás engedélyezése
CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(250) NOT NULL,
    password VARCHAR(250) NOT NULL,
    reset_token VARCHAR(44) NULL DEFAULT NULL,
    reset_token_expiry DATETIME NULL DEFAULT NULL,
    deleted_at DATETIME NULL DEFAULT NULL,
    UNIQUE KEY uq_users_username (username),
    UNIQUE KEY uq_users_email (email),
    KEY idx_users_reset_token (reset_token),
    KEY idx_users_deleted_at (deleted_at)
)
ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sessions tábla: session_id egyedi, index az expiry mezőn a takarító lekérdezésekhez, index a user mezőn a keresésekhez
CREATE TABLE IF NOT EXISTS sessions (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    `user` INT UNSIGNED NOT NULL,
    session_id VARCHAR(44) NOT NULL,
    expiry DATETIME NOT NULL,
    UNIQUE KEY uq_sessions_session_id (session_id),
    KEY idx_sessions_user (`user`),
    KEY idx_sessions_expiry (expiry),
    CONSTRAINT fk_sessions_user FOREIGN KEY (`user`) REFERENCES users(id)
)
ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Vehicles tábla: index a user és license_plate mezőkön, következetes elnevezések használata
CREATE TABLE IF NOT EXISTS vehicles (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    `user` INT UNSIGNED NOT NULL,
    brand VARCHAR(20) NOT NULL,
    model VARCHAR(20) NOT NULL,
    license_plate VARCHAR(10) NOT NULL,
    `year` INT NOT NULL,
    consumption FLOAT NOT NULL,
    emission FLOAT NOT NULL,
    UNIQUE KEY uq_vehicles_license_plate (license_plate),
    KEY idx_vehicles_user (`user`),
    CONSTRAINT fk_vehicles_user FOREIGN KEY (`user`) REFERENCES users(id)
)
ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Ellenőrizze, hogy az eseményütemező engedélyezve van (néhány környezetben SUPER vagy megfelelő jogosultság szükséges)
SET GLOBAL event_scheduler = ON;

-- Napi takarítás a lejárt sessionökre. Egyszerű, egyutas DO használata, így nem kell delimiter-t váltani.
CREATE EVENT IF NOT EXISTS SessionCleanupEvent
ON SCHEDULE EVERY 1 DAY
COMMENT 'Kitörli a lejárt sessionöket'
DO
  DELETE FROM sessions WHERE expiry <= NOW();

-- Heti optimalizálás az összes táblán a lekérdezési teljesítmény javításához

CREATE EVENT IF NOT EXISTS WeeklyTableOptimize
ON SCHEDULE EVERY 7 DAY
STARTS DATE_ADD(NOW(), INTERVAL 3 DAY)
COMMENT 'Optimalizálja az összes InnoDB táblát hetente'
DO
  BEGIN
    OPTIMIZE TABLE users;
    OPTIMIZE TABLE sessions;
    OPTIMIZE TABLE vehicles;
  END;

-- Havi statisztika frissítés az összes táblán a lekérdezéstervező számára
CREATE EVENT IF NOT EXISTS MonthlyTableAnalyze
ON SCHEDULE EVERY 30 DAY
STARTS DATE_ADD(NOW(), INTERVAL 5 DAY)
COMMENT 'Frissíti az összes tábla statisztikáit havonta'
DO
  BEGIN
    ANALYZE TABLE users;
    ANALYZE TABLE sessions;
    ANALYZE TABLE vehicles;
  END;

-- Napi index ellenőrzés az integritási hibákra
CREATE EVENT IF NOT EXISTS DailyCheckIntegrity
ON SCHEDULE EVERY 1 DAY
STARTS DATE_ADD(NOW(), INTERVAL 1 DAY)
COMMENT 'Ellenőrzi a táblák integritását napi szinten'
DO
  BEGIN
    CHECK TABLE users;
    CHECK TABLE sessions;
    CHECK TABLE vehicles;
  END;

-- Megjegyzések:
-- - A `sessions.expiry` és `sessions.user` indexek gyorsítják a takarítást és a keresést, és lehetővé teszik további, session-hoz kapcsolódó események hozzáadását teljes bejárás nélkül.
-- - Mindkét esemény idempotens (CREATE EVENT IF NOT EXISTS), így hasonlóan további eseményeket adhatsz hozzá, pl. 'SessionIndexEvent' vagy 'SessionArchiveEvent'.
-- - Ha később újra szeretnéd indexelni vagy újjáépíteni az indexeket, hozzáadhatsz egy eseményt 'OPTIMIZE TABLE' vagy 'ALTER TABLE ... FORCE' használatával (nagy táblákon óvatosan).
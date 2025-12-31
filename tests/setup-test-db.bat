@echo off
REM EcoDrive Test Database Setup Script for Windows
REM Ez a script létrehozza a test adatbázist és felhasználót Windows-on

echo ===================================
echo EcoDrive Test Database Setup
echo ===================================
echo.

REM Konfiguráció
set DB_HOST=localhost
set DB_ROOT_PASSWORD=root
set TEST_DB_NAME=ecodrive_test
set TEST_DB_USER=ecodrive_test
set TEST_DB_PASSWORD=ecodrive_test

echo Konfiguráció:
echo   Host: %DB_HOST%
echo   Test DB: %TEST_DB_NAME%
echo   Test User: %TEST_DB_USER%
echo.

REM MySQL ellenőrzése
echo MySQL ellenőrzése...
mysql -h %DB_HOST% -u root -p%DB_ROOT_PASSWORD% -e "SELECT 1;" >nul 2>&1
if errorlevel 1 (
    echo ❌ Hiba: Nem sikerült csatlakozni a MySQL-hez!
    echo Ellenőrizd a következőket:
    echo   - MySQL service fut-e
    echo   - Root jelszó helyes-e
    pause
    exit /b 1
)
echo ✓ MySQL kapcsolat OK
echo.

REM Test adatbázis létrehozása
echo Test adatbázis létrehozása...
mysql -h %DB_HOST% -u root -p%DB_ROOT_PASSWORD% -e "DROP DATABASE IF EXISTS %TEST_DB_NAME%; CREATE DATABASE %TEST_DB_NAME% DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;"
if errorlevel 1 (
    echo ❌ Hiba az adatbázis létrehozásakor!
    pause
    exit /b 1
)
echo ✓ Adatbázis létrehozva: %TEST_DB_NAME%
echo.

REM Test user létrehozása
echo Test user létrehozása...
mysql -h %DB_HOST% -u root -p%DB_ROOT_PASSWORD% -e "DROP USER IF EXISTS '%TEST_DB_USER%'@'localhost'; DROP USER IF EXISTS '%TEST_DB_USER%'@'%%';"
mysql -h %DB_HOST% -u root -p%DB_ROOT_PASSWORD% -e "CREATE USER '%TEST_DB_USER%'@'localhost' IDENTIFIED BY '%TEST_DB_PASSWORD%'; CREATE USER '%TEST_DB_USER%'@'%%' IDENTIFIED BY '%TEST_DB_PASSWORD%';"
mysql -h %DB_HOST% -u root -p%DB_ROOT_PASSWORD% -e "GRANT ALL PRIVILEGES ON %TEST_DB_NAME%.* TO '%TEST_DB_USER%'@'localhost'; GRANT ALL PRIVILEGES ON %TEST_DB_NAME%.* TO '%TEST_DB_USER%'@'%%'; FLUSH PRIVILEGES;"
if errorlevel 1 (
    echo ❌ Hiba a user létrehozásakor!
    pause
    exit /b 1
)
echo ✓ User létrehozva: %TEST_DB_USER%
echo.

REM .env.test fájl létrehozása
echo Test környezeti változók beállítása...
(
echo DB_HOST=%DB_HOST%
echo DB_USER=%TEST_DB_USER%
echo DB_PASSWORD=%TEST_DB_PASSWORD%
echo DB_NAME=%TEST_DB_NAME%
echo DB_PORT=3306
echo DB_DATETIME_FORMAT="Y-m-d G:i:s"
echo DB_DATETIME_TIMEZONE=UTC
echo.
echo WWW_HOST=localhost
echo.
echo SESSION_COOKIE_NAME=ECODRIVE_TEST_SESSION
echo.
echo DEBUG_MODE=true
) > ..\.env.test
echo ✓ .env.test fájl létrehozva/frissítve
echo.

echo ===================================
echo ✓ Setup befejezve!
echo ===================================
echo.
echo Most már futtathatod a teszteket:
echo   composer test
echo   composer test:unit
echo   composer test:integration
echo.
echo Adatbázis információk:
echo   Adatbázis: %TEST_DB_NAME%
echo   Felhasználó: %TEST_DB_USER%
echo   Jelszó: %TEST_DB_PASSWORD%
echo   Host: %DB_HOST%
echo.
pause

#!/bin/bash

# EcoDrive Test Database Setup Script
# Ez a script létrehozza a test adatbázist és felhasználót

set -e  # Megáll hiba esetén

echo "==================================="
echo "EcoDrive Test Database Setup"
echo "==================================="
echo ""

# Konfiguráció
DB_HOST="${DB_HOST:-localhost}"
DB_ROOT_PASSWORD="${DB_ROOT_PASSWORD:-root}"
TEST_DB_NAME="ecodrive_test"
TEST_DB_USER="ecodrive_test"
TEST_DB_PASSWORD="ecodrive_test"

echo "Konfiguráció:"
echo "  Host: $DB_HOST"
echo "  Test DB: $TEST_DB_NAME"
echo "  Test User: $TEST_DB_USER"
echo ""

# MySQL kapcsolat ellenőrzése
echo "MySQL kapcsolat ellenőrzése..."
if ! mysql -h "$DB_HOST" -u root -p"$DB_ROOT_PASSWORD" -e "SELECT 1;" > /dev/null 2>&1; then
    echo "❌ Hiba: Nem sikerült csatlakozni a MySQL-hez!"
    echo "Ellenőrizd a következőket:"
    echo "  - MySQL service fut-e: systemctl status mysql"
    echo "  - Root jelszó helyes-e"
    exit 1
fi
echo "✅ MySQL kapcsolat OK"
echo ""

# Test adatbázis létrehozása
echo "Test adatbázis létrehozása..."
mysql -h "$DB_HOST" -u root -p"$DB_ROOT_PASSWORD" <<EOF
DROP DATABASE IF EXISTS $TEST_DB_NAME;
CREATE DATABASE $TEST_DB_NAME DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
EOF
echo "✅ Adatbázis létrehozva: $TEST_DB_NAME"
echo ""

# Test user létrehozása
echo "Test user létrehozása..."
mysql -h "$DB_HOST" -u root -p"$DB_ROOT_PASSWORD" <<EOF
DROP USER IF EXISTS '$TEST_DB_USER'@'localhost';
DROP USER IF EXISTS '$TEST_DB_USER'@'%';
CREATE USER '$TEST_DB_USER'@'localhost' IDENTIFIED BY '$TEST_DB_PASSWORD';
CREATE USER '$TEST_DB_USER'@'%' IDENTIFIED BY '$TEST_DB_PASSWORD';
GRANT ALL PRIVILEGES ON $TEST_DB_NAME.* TO '$TEST_DB_USER'@'localhost';
GRANT ALL PRIVILEGES ON $TEST_DB_NAME.* TO '$TEST_DB_USER'@'%';
FLUSH PRIVILEGES;
EOF
echo "✅ User létrehozva: $TEST_DB_USER"
echo ""

# Táblák létrehozása a schema alapján
echo "Adatbázis séma importálása..."
SCHEMA_FILE="../app/database/database.sql"

if [ -f "$SCHEMA_FILE" ]; then
    # Csak a CREATE TABLE és INSERT utasításokat használjuk
    cat "$SCHEMA_FILE" | \
        grep -v "CREATE USER" | \
        grep -v "DROP DATABASE" | \
        grep -v "CREATE DATABASE" | \
        grep -v "USE ecodrive" | \
        grep -v "GRANT ALL" | \
        sed "s/USE ecodrive;/USE $TEST_DB_NAME;/g" | \
        mysql -h "$DB_HOST" -u root -p"$DB_ROOT_PASSWORD" "$TEST_DB_NAME"
    
    echo "✅ Séma importálva"
else
    echo "⚠️  Figyelmeztetés: Schema fájl nem található: $SCHEMA_FILE"
    echo "A tesztek futtatásakor a bootstrap.php fogja létrehozni a táblákat."
fi
echo ""

# Ellenőrzés
echo "Ellenőrzés..."
TABLE_COUNT=$(mysql -h "$DB_HOST" -u "$TEST_DB_USER" -p"$TEST_DB_PASSWORD" "$TEST_DB_NAME" -sN -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = '$TEST_DB_NAME';")
echo "✅ Létrehozott táblák száma: $TABLE_COUNT"
echo ""

# .env.test fájl létrehozása/frissítése
echo "Test környezeti változók beállítása..."
ENV_TEST_FILE="../.env.test"

cat > "$ENV_TEST_FILE" <<EOF
DB_HOST=$DB_HOST
DB_USER=$TEST_DB_USER
DB_PASSWORD=$TEST_DB_PASSWORD
DB_NAME=$TEST_DB_NAME
DB_PORT=3306
DB_DATETIME_FORMAT="Y-m-d G:i:s"
DB_DATETIME_TIMEZONE=UTC

WWW_HOST=localhost

SESSION_COOKIE_NAME=ECODRIVE_TEST_SESSION

DEBUG_MODE=true
EOF

echo "✅ .env.test fájl létrehozva/frissítve"
echo ""

echo "==================================="
echo "✅ Setup befejezve!"
echo "==================================="
echo ""
echo "Most már futtathatod a teszteket:"
echo "  composer test"
echo "  composer test:unit"
echo "  composer test:integration"
echo ""
echo "Adatbázis információk:"
echo "  Adatbázis: $TEST_DB_NAME"
echo "  Felhasználó: $TEST_DB_USER"
echo "  Jelszó: $TEST_DB_PASSWORD"
echo "  Host: $DB_HOST"
echo ""

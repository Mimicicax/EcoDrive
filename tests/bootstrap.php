<?php

/**
 * PHPUnit Bootstrap File
 * Ez a fájl inicializálja a tesztkörnyezetet
 */

// Autoloader betöltése
require_once __DIR__ . '/../vendor/autoload.php';

// Test környezeti változók beállítása
if (!file_exists(__DIR__ . '/../.env.test')) {
    // Ha nincs .env.test fájl, használjuk a környezeti változókat
    $_ENV['DB_HOST'] = getenv('DB_HOST') ?: 'localhost';
    $_ENV['DB_USER'] = getenv('DB_USER') ?: 'ecodrive_test';
    $_ENV['DB_PASSWORD'] = getenv('DB_PASSWORD') ?: 'ecodrive_test';
    $_ENV['DB_NAME'] = getenv('DB_NAME') ?: 'ecodrive_test';
    $_ENV['DB_PORT'] = getenv('DB_PORT') ?: '3306';
} else {
    // .env.test fájl betöltése ha létezik
    $envTest = parse_ini_file(__DIR__ . '/../.env.test');
    foreach ($envTest as $key => $value) {
        $_ENV[$key] = $value;
    }
}

// Error reporting beállítása
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Időzóna beállítása
date_default_timezone_set('UTC');

// Test helper függvények
function createTestDatabase() {
    $conn = mysqli_connect(
        $_ENV['DB_HOST'],
        $_ENV['DB_USER'],
        $_ENV['DB_PASSWORD'],
        null,
        $_ENV['DB_PORT']
    );

    if (!$conn) {
        throw new Exception("Nem sikerült csatlakozni az adatbázishoz: " . mysqli_connect_error());
    }

    // Test adatbázis létrehozása
    $dbName = $_ENV['DB_NAME'];
    mysqli_query($conn, "DROP DATABASE IF EXISTS `{$dbName}`");
    mysqli_query($conn, "CREATE DATABASE `{$dbName}` DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    mysqli_select_db($conn, $dbName);

    // Táblák létrehozása
    $schema = file_get_contents(__DIR__ . '/../app/database/database.sql');
    
    // SQL utasítások szétválasztása és végrehajtása
    $statements = array_filter(
        array_map('trim', explode(';', $schema)),
        function($stmt) {
            return !empty($stmt) && 
                   !preg_match('/^(CREATE USER|GRANT|DROP DATABASE|CREATE DATABASE|USE)/i', $stmt);
        }
    );

    foreach ($statements as $statement) {
        if (!empty($statement)) {
            mysqli_query($conn, $statement);
        }
    }

    mysqli_close($conn);
}

function dropTestDatabase() {
    $conn = mysqli_connect(
        $_ENV['DB_HOST'],
        $_ENV['DB_USER'],
        $_ENV['DB_PASSWORD'],
        null,
        $_ENV['DB_PORT']
    );

    if ($conn) {
        mysqli_query($conn, "DROP DATABASE IF EXISTS `{$_ENV['DB_NAME']}`");
        mysqli_close($conn);
    }
}

<?php
/**
 * Seeder for large dataset (users, vehicles, sessions)
 *
 * Usage:
 *  php scripts/seed_large_dataset.php --users=50000 --vehicles-per-user=2 --batch=1000
 *  php scripts/seed_large_dataset.php --action=all --sql-file=./seed_dump.sql --yes
 *
 * Options:
 *  --users: number of users to create (default 50000)
 *  --vehicles-per-user: vehicles per user (default 2)
 *  --batch: batch size for inserts (default 1000)
 *  --action: seed|create-archive-table|archive-sessions|create-events|analyze|optimize|truncate|all (default seed)
 *  --dump-sql: boolean flag to enable SQL dump mode
 *  --sql-file: path to SQL file (when using --dump-sql); defaults to scripts/seed_dump.sql
 *  --yes: skip interactive confirmation
 *
 * Environment variables (optional): DB_HOST, DB_NAME, DB_USER, DB_PASS
 */

ini_set('memory_limit', '1G');

$opts = getopt('', ['users::', 'vehicles-per-user::', 'batch::', 'yes', 'action::', 'sql-file::', 'dump-sql']);
$numUsers = isset($opts['users']) ? (int)$opts['users'] : 50000;
$vehiclesPerUser = isset($opts['vehicles-per-user']) ? (int)$opts['vehicles-per-user'] : 2;
$batch = isset($opts['batch']) ? (int)$opts['batch'] : 1000;
$autoYes = isset($opts['yes']);
$action = isset($opts['action']) ? $opts['action'] : 'seed';
// SQL dump options
$dumpSql = isset($opts['dump-sql']);
$sqlFile = isset($opts['sql-file']) ? $opts['sql-file'] : ($dumpSql ? __DIR__ . '/seed_dump.sql' : null);

$dbHost = getenv('DB_HOST') ?: '127.0.0.1';
$dbName = getenv('DB_NAME') ?: 'ecodrive';
$dbUser = getenv('DB_USER') ?: 'ecodrive';
$dbPass = getenv('DB_PASS') ?: 'ecodrive2026';

echo "Seeder will insert {$numUsers} users, approx " . ($numUsers * $vehiclesPerUser) . " vehicles.\n";
if (!$autoYes) {
    echo "Action: {$action}\n";
    echo "Type YES to continue: ";
    $line = trim(fgets(STDIN));
    if ($line !== 'YES') {
        echo "Aborted.\n";
        exit(1);
    }
}

try {
    $dsn = "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4";
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (Exception $e) {
    echo "DB connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Disable foreign key checks for bulk load performance when running directly
$pdo->exec('SET FOREIGN_KEY_CHECKS=0');

// Helper routines so actions can be composed
function createArchivedSessionsTable(PDO $pdo) {
    $sql = <<<SQL
CREATE TABLE IF NOT EXISTS archived_sessions (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    `user` INT UNSIGNED NULL,
    session_id VARCHAR(128) NOT NULL,
    expiry DATETIME NOT NULL,
    archived_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_archived_sessions_user (`user`),
    KEY idx_archived_sessions_archived_at (archived_at),
    CONSTRAINT fk_archived_sessions_user FOREIGN KEY (`user`) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
    $pdo->exec($sql);
    echo "Ensured archived_sessions table exists.\n";
}

function seedUsers(PDO $pdo, int $numUsers, int $batch, $outFile = null) {
    echo "Inserting users in batches of {$batch}...\n";
    $nextId = 1;
    for ($start = 1; $start <= $numUsers; $start += $batch) {
        $currentBatch = min($batch, $numUsers - $start + 1);
        $values = [];
        $placeholders = [];
        for ($i = 0; $i < $currentBatch; $i++) {
            $id = $nextId++;
            $username = 'user' . str_pad($id, 6, '0', STR_PAD_LEFT);
            $email = $username . '@example.com';
            $passwordHash = password_hash('password' . $id, PASSWORD_DEFAULT);
            $values[] = $id;
            $values[] = $username;
            $values[] = $email;
            $values[] = $passwordHash;
            $placeholders[] = '(?, ?, ?, ?)';
        }

        if ($outFile) {
            $rows = [];
            for ($i = 0, $j = 0; $i < $currentBatch; $i++, $j += 4) {
                $idVal = (int)$values[$j];
                $u = $pdo->quote($values[$j+1]);
                $e = $pdo->quote($values[$j+2]);
                $p = $pdo->quote($values[$j+3]);
                $rows[] = "({$idVal}, {$u}, {$e}, {$p})";
            }
            fwrite($outFile, "INSERT INTO users (id, username, email, password) VALUES\n" . implode(',\n', $rows) . ";\n\n");
            echo sprintf("Wrote users %d - %d to SQL file\n", $start, $start + $currentBatch - 1);
        } else {
            $sql = 'INSERT INTO users (id, username, email, password) VALUES ' . implode(', ', $placeholders);
            $pdo->beginTransaction();
            $stmt = $pdo->prepare($sql);
            $stmt->execute($values);
            $pdo->commit();
            echo sprintf("Inserted users %d - %d\n", $start, $start + $currentBatch - 1);
        }
    }
}

function seedVehicles(PDO $pdo, int $numUsers, int $vehiclesPerUser, int $batch, $outFile = null) {
    echo "Inserting vehicles ({$vehiclesPerUser} per user) in batches...\n";
    $totalVehicles = $numUsers * $vehiclesPerUser;
    $vehicleBatch = $batch; // rows per batch refer to vehicle rows
    $vehicleInserted = 0;

    $pdo->beginTransaction();
    $vehicleSqlBase = 'INSERT INTO vehicles (`user`, brand, model, license_plate, `year`, consumption) VALUES ';
    $vehiclePlaceholders = [];
    $vehicleValues = [];

    for ($userId = 1; $userId <= $numUsers; $userId++) {
        for ($v = 1; $v <= $vehiclesPerUser; $v++) {
            $brand = 'Brand' . (($userId + $v) % 20 + 1);
            $model = 'Model' . (($userId + $v) % 50 + 1);
            $license = sprintf('LP%06d%02d', $userId, $v);
            $year = 2000 + ($userId % 25);
            $consumption = round(3 + (($userId + $v) % 100) / 20, 2);

            $vehiclePlaceholders[] = '(?, ?, ?, ?, ?, ?)';
            array_push($vehicleValues, $userId, $brand, $model, $license, $year, $consumption);
            $vehicleInserted++;

            if (count($vehiclePlaceholders) >= $vehicleBatch) {
                if ($outFile) {
                    $rows = [];
                    for ($i = 0, $j = 0; $i < count($vehiclePlaceholders); $i++, $j += 6) {
                        $u = (int)$vehicleValues[$j];
                        $b = $pdo->quote($vehicleValues[$j+1]);
                        $m = $pdo->quote($vehicleValues[$j+2]);
                        $lp = $pdo->quote($vehicleValues[$j+3]);
                        $yr = (int)$vehicleValues[$j+4];
                        $c = (float)$vehicleValues[$j+5];
                        $rows[] = "({$u}, {$b}, {$m}, {$lp}, {$yr}, {$c})";
                    }
                    fwrite($outFile, $vehicleSqlBase . "\n" . implode(',\n', $rows) . ";\n\n");
                    echo "Wrote vehicles: {$vehicleInserted}/{$totalVehicles} to SQL file\n";
                    $vehiclePlaceholders = [];
                    $vehicleValues = [];
                } else {
                    $sql = $vehicleSqlBase . implode(', ', $vehiclePlaceholders);
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($vehicleValues);
                    $pdo->commit();
                    echo "Inserted vehicles: {$vehicleInserted}/{$totalVehicles}\n";
                    // reset
                    $vehiclePlaceholders = [];
                    $vehicleValues = [];
                    $pdo->beginTransaction();
                }
            }
        }
    }

    // flush remaining vehicles
    if (!empty($vehiclePlaceholders)) {
        if ($outFile) {
            $rows = [];
            for ($i = 0, $j = 0; $i < count($vehiclePlaceholders); $i++, $j += 6) {
                $u = (int)$vehicleValues[$j];
                $b = $pdo->quote($vehicleValues[$j+1]);
                $m = $pdo->quote($vehicleValues[$j+2]);
                $lp = $pdo->quote($vehicleValues[$j+3]);
                $yr = (int)$vehicleValues[$j+4];
                $c = (float)$vehicleValues[$j+5];
                $rows[] = "({$u}, {$b}, {$m}, {$lp}, {$yr}, {$c})";
            }
            fwrite($outFile, $vehicleSqlBase . "\n" . implode(',\n', $rows) . ";\n\n");
            echo "Wrote vehicles: {$vehicleInserted}/{$totalVehicles} to SQL file\n";
        } else {
            $sql = $vehicleSqlBase . implode(', ', $vehiclePlaceholders);
            $stmt = $pdo->prepare($sql);
            $stmt->execute($vehicleValues);
            $pdo->commit();
            echo "Inserted vehicles: {$vehicleInserted}/{$totalVehicles}\n";
        }
    }
}

function seedSessions(PDO $pdo, int $sessionCount, int $sessBatch, $outFile = null) {
    echo "Inserting sessions for first {$sessionCount} users...\n";
    $sessPlaceholders = [];
    $sessValues = [];
    $insertedSess = 0;
    $pdo->beginTransaction();
    for ($uid = 1; $uid <= $sessionCount; $uid++) {
        $sessionId = bin2hex(random_bytes(22)); // 44 hex chars
        $expiry = (new DateTime('+30 days'))->format('Y-m-d H:i:s');
        $sessPlaceholders[] = '(?, ?, ?)';
        array_push($sessValues, $uid, $sessionId, $expiry);
        $insertedSess++;
        if (count($sessPlaceholders) >= $sessBatch) {
            if ($outFile) {
                $rows = [];
                for ($i = 0, $j = 0; $i < count($sessPlaceholders); $i++, $j += 3) {
                    $u = (int)$sessValues[$j];
                    $sid = $pdo->quote($sessValues[$j+1]);
                    $ex = $pdo->quote($sessValues[$j+2]);
                    $rows[] = "({$u}, {$sid}, {$ex})";
                }
                fwrite($outFile, 'INSERT INTO sessions (`user`, session_id, expiry) VALUES\n' . implode(',\n', $rows) . ";\n\n");
                echo "Wrote sessions: {$insertedSess}/{$sessionCount} to SQL file\n";
                $sessPlaceholders = [];
                $sessValues = [];
            } else {
                $sql = 'INSERT INTO sessions (`user`, session_id, expiry) VALUES ' . implode(', ', $sessPlaceholders);
                $stmt = $pdo->prepare($sql);
                $stmt->execute($sessValues);
                $pdo->commit();
                echo "Inserted sessions: {$insertedSess}/{$sessionCount}\n";
                $sessPlaceholders = [];
                $sessValues = [];
                $pdo->beginTransaction();
            }
        }
    }
    if (!empty($sessPlaceholders)) {
        if ($outFile) {
            $rows = [];
            for ($i = 0, $j = 0; $i < count($sessPlaceholders); $i++, $j += 3) {
                $u = (int)$sessValues[$j];
                $sid = $pdo->quote($sessValues[$j+1]);
                $ex = $pdo->quote($sessValues[$j+2]);
                $rows[] = "({$u}, {$sid}, {$ex})";
            }
            fwrite($outFile, 'INSERT INTO sessions (`user`, session_id, expiry) VALUES\n' . implode(',\n', $rows) . ";\n\n");
            echo "Wrote sessions: {$insertedSess}/{$sessionCount} to SQL file\n";
        } else {
            $sql = 'INSERT INTO sessions (`user`, session_id, expiry) VALUES ' . implode(', ', $sessPlaceholders);
            $stmt = $pdo->prepare($sql);
            $stmt->execute($sessValues);
            $pdo->commit();
            echo "Inserted sessions: {$insertedSess}/{$sessionCount}\n";
        }
    }
    return $insertedSess;
}

function createSessionArchiveEvent(PDO $pdo) {
    $single = "CREATE EVENT IF NOT EXISTS SessionArchiveEvent ON SCHEDULE EVERY 1 DAY COMMENT 'Áthelyezi a lejárt sessionöket az archived_sessions táblába' DO INSERT INTO archived_sessions (`user`, session_id, expiry, archived_at) SELECT `user`, session_id, expiry, NOW() FROM sessions WHERE expiry <= NOW();";
    $pdo->exec($single);
    echo "Created SessionArchiveEvent (if not exists).\n";
}

function archiveExpiredSessions(PDO $pdo) {
    // Move expired sessions to archived_sessions and delete them from sessions
    $pdo->beginTransaction();
    $ins = $pdo->prepare('INSERT INTO archived_sessions (`user`, session_id, expiry, archived_at) SELECT `user`, session_id, expiry, NOW() FROM sessions WHERE expiry <= NOW()');
    $del = $pdo->prepare('DELETE FROM sessions WHERE expiry <= NOW()');
    $ins->execute();
    $moved = $ins->rowCount();
    $del->execute();
    $pdo->commit();
    echo "Archived {$moved} expired sessions.\n";
}

function analyzeTables(PDO $pdo, array $tables) {
    foreach ($tables as $t) {
        $pdo->exec('ANALYZE TABLE ' . $t);
        echo "Analyzed {$t}\n";
    }
}

function optimizeTables(PDO $pdo, array $tables) {
    foreach ($tables as $t) {
        $pdo->exec('OPTIMIZE TABLE ' . $t);
        echo "Optimized {$t}\n";
    }
}

function truncateTables(PDO $pdo, array $tables) {
    foreach ($tables as $t) {
        $pdo->exec('TRUNCATE TABLE ' . $t);
        echo "Truncated {$t}\n";
    }
}

// Prepare SQL dump file if requested
$outFileHandle = null;
if ($sqlFile) {
    $outFileHandle = fopen($sqlFile, 'w');
    if (!$outFileHandle) { echo "Failed to open SQL file for writing: {$sqlFile}\n"; exit(1); }
    fwrite($outFileHandle, "-- EcoDrive SQL seed dump\nSET FOREIGN_KEY_CHECKS=0;\nSTART TRANSACTION;\n\n");
}

// Decide actions based on --action
if ($action === 'seed' || $action === 'all') {
    seedUsers($pdo, $numUsers, $batch, $outFileHandle);
    seedVehicles($pdo, $numUsers, $vehiclesPerUser, $batch, $outFileHandle);
    $insertedSess = seedSessions($pdo, min(1000, $numUsers), 500, $outFileHandle);
    echo "Seeding complete. Users: {$numUsers}, Vehicles: " . ($numUsers * $vehiclesPerUser) . ", Sessions: {$insertedSess}\n";
}

if ($action === 'create-archive-table' || $action === 'all') {
    createArchivedSessionsTable($pdo);
}

if ($action === 'archive-sessions' || $action === 'all') {
    // ensure table exists
    createArchivedSessionsTable($pdo);
    archiveExpiredSessions($pdo);
}

if ($action === 'create-events' || $action === 'all') {
    createSessionArchiveEvent($pdo);
}

if ($action === 'analyze' || $action === 'all') {
    analyzeTables($pdo, ['users', 'vehicles', 'sessions', 'archived_sessions']);
}

if ($action === 'optimize' || $action === 'all') {
    optimizeTables($pdo, ['users', 'vehicles', 'sessions', 'archived_sessions']);
}

if ($action === 'truncate') {
    if (!$autoYes) {
        echo "Are you sure you want to TRUNCATE users, vehicles, sessions, archived_sessions? Type YES: ";
        $confirm = trim(fgets(STDIN));
        if ($confirm !== 'YES') { echo "Aborted.\n"; exit(1); }
    }
    truncateTables($pdo, ['archived_sessions', 'sessions', 'vehicles', 'users']);
}

// Re-enable foreign key checks
$pdo->exec('SET FOREIGN_KEY_CHECKS=1');

if ($outFileHandle) {
    fwrite($outFileHandle, "COMMIT;\nSET FOREIGN_KEY_CHECKS=1;\n");
    fclose($outFileHandle);
    echo "SQL dump written to {$sqlFile}\n";
}

// If action was handled above and included seed, we already printed summary. If action specified other single actions, print done.
if (!in_array($action, ['seed', 'all'])) {
    echo "Action '{$action}' completed.\n";
}

exit(0);

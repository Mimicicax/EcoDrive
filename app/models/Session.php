<?php

namespace EcoDrive\Models;

use DateInterval;
use DateTimeZone;
use mysqli_result;
use function EcoDrive\Environment\appConfig;
use DateTimeImmutable;

require_once "config.php";
require_once appConfig()->APP_ROOT . "/models/User.php";

class Session {

    public int $id;
    public User $user;
    public string $sessionId;
    public DateTimeImmutable $sessionExpiry;

    private const queryCurrentSession = 
    "SELECT sessions.id AS sid, sessions.session_id, sessions.expiry, 
            users.id, users.username, users.email, users.password, users.reset_token, users.reset_token_expiry 
     FROM sessions 
     INNER JOIN users on sessions.user = users.id 
     WHERE sessions.session_id LIKE ? AND sessions.expiry > CURRENT_TIMESTAMP()";

    private const deleteSessionByUser = "DELETE FROM sessions WHERE user=?";
    private const insertSession = "INSERT INTO sessions (user, session_id, expiry) VALUES(?, ?, ?)";

    private function __construct(int $id, User $user, string $sessionId, DateTimeImmutable $sessionExpiry) {
        $this->id = $id;
        $this->user = $user;
        $this->sessionId = $sessionId;
        $this->sessionExpiry = $sessionExpiry;
    }

    public static function currentSession(): ?Session {
        static $current = null;

        // Már van session
        if (isset($current))
            return $current;

        // Nincs session cookie
        if (!isset($_COOKIE[appConfig()->SESSION_COOKIE_NAME]))
            return null;

        $sessId = $_COOKIE[appConfig()->SESSION_COOKIE_NAME];

        if (!($query = mysqli_prepare(appConfig()->DB_CONN, Session::queryCurrentSession)) || 
            !mysqli_stmt_bind_param($query, "s", $sessId) ||
            !mysqli_stmt_execute($query)) {

            // Nincs érvényes session az adatbázis szerint
            mysqli_stmt_close($query);
            return null;
        }

        $result = mysqli_stmt_get_result($query);

        if ($result === false) {
            mysqli_stmt_close($query);
            return null;
        }

        $fields = mysqli_fetch_assoc($result);
        
        mysqli_free_result($result);
        mysqli_stmt_close($query);
        
        if (empty($fields))
            return null;

        $user = new User($fields);
        $expiry = DateTimeImmutable::createFromFormat(appConfig()->DB_DATETIME_FORMAT, $fields["expiry"], appConfig()->DB_DATETIME_TIMEZONE);
        $current = new Session($fields["sid"], $user, $fields["session_id"], $expiry);

        return $current;
    }

    public static function currentUser() { 
        return Session::currentSession()?->user;
    }

    public static function isAuthenticated() { 
        return Session::currentSession() !== null;
    }

    public static function createSessionForUser(User $user) {

        // Először töröljük a lejárt sessiont, ha van ilyen.
        if (!($stmt = mysqli_prepare(appConfig()->DB_CONN, Session::deleteSessionByUser)) ||
            !mysqli_stmt_bind_param($stmt, "i", $user->id) ||
            !mysqli_stmt_execute($stmt)) {

            mysqli_stmt_close($stmt);
            return;
        }

        mysqli_stmt_close($stmt);

        // Utána pedig hozzáadjuk az újat. 1 hétig érvényes
        $sessId = base64_encode(random_bytes(32));
        $expiry = (new DateTimeImmutable("now", appConfig()->DB_DATETIME_TIMEZONE))
            ->add(new DateInterval("P7D"));

        $expiryString = $expiry->format(appConfig()->DB_DATETIME_FORMAT);

        if (!($stmt = mysqli_prepare(appConfig()->DB_CONN, Session::insertSession)) ||
            !mysqli_stmt_bind_param($stmt, "iss", $user->id, $sessId, $expiryString) ||
            !mysqli_stmt_execute($stmt)) {

            mysqli_stmt_close($stmt);
            return;
        }

        mysqli_stmt_close(statement: $stmt);

        // Végül beállítjuk a sütit
        setcookie(appConfig()->SESSION_COOKIE_NAME, $sessId, $expiry->getTimestamp(), "/", "", false, true);
    }
} 
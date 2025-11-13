<?php

namespace EcoDrive\Models;

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
    "SELECT sessions.id, sessions.sessionId, sessions.expiry, 
            users.id, users.username, users.email, users.password, users.salt, users.reset_token, users.reset_token_expiry 
     FROM sessions 
     INNER JOIN users on sessions.user = users.id 
     WHERE sessions.sessionId LIKE ? AND sessions.expiry > CURRENT_TIMESTAMP()";

    public function __construct(int $id, User $user, string $sessionId, DateTimeImmutable $sessionExpiry) {
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

        $query = mysqli_stmt_init(appConfig()->DB_CONN);
        $sessId = $_COOKIE[appConfig()->SESSION_COOKIE_NAME];

        if (!mysqli_stmt_prepare($query, Session::queryCurrentSession) || 
            !mysqli_stmt_bind_param($query, "s", $sessId) ||
            !mysqli_stmt_execute($query)) {

            // Nincs érvényes session az adatbázis szerint
            mysqli_stmt_close($query);
            return null;
        }

        $result = mysqli_stmt_get_result($query);
        $fields = mysqli_fetch_assoc($result);
        
        mysqli_free_result($result);
        mysqli_stmt_close($query);

        $user = new User($fields["users.id"], $fields["users.username"], $fields["users.email"], $fields["password"], $fields["users.salt"], $fields["users.reset_token"], $fields["users.reset_token_expiry"]);
        $current = new Session($fields["sessions.id"], $user, $fields["sessions.sessionId"], $fields["sessions.expiry"]);

        return $current;
    }

    public static function currentUser() { 
        return Session::currentSession()?->user;
    }

    public static function isAuthenticated() { 
        return Session::currentSession() !== null;
    }
}
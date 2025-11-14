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
    public ?DateTimeImmutable $sessionExpiry;
    private static $currentSession = null;

    private const queryCurrentSession = 
    "SELECT sessions.id AS sid, sessions.session_id, sessions.expiry, 
            users.id, users.username, users.email, users.password, users.reset_token, users.reset_token_expiry 
     FROM sessions 
     INNER JOIN users on sessions.user = users.id 
     WHERE sessions.session_id LIKE ? AND sessions.expiry > CURRENT_TIMESTAMP()";
    
    private const queryActiveSession =
    "SELECT sessions.id AS sid, sessions.session_id, sessions.expiry, 
            users.id, users.username, users.email, users.password, users.reset_token, users.reset_token_expiry 
     FROM sessions 
     INNER JOIN users on sessions.user = users.id 
     WHERE sessions.user = ? AND sessions.expiry > CURRENT_TIMESTAMP()";

    private const deleteSessionByUser = "DELETE FROM sessions WHERE user=?";
    private const insertSession = "INSERT INTO sessions (user, session_id, expiry) VALUES(?, ?, ?)";

    private function __construct($fields) {
        $this->id = $fields["sid"] ?? -1;
        $this->user = new User($fields);
        $this->sessionId = $fields["session_id"] ?? "";

        $date = DateTimeImmutable::createFromFormat(appConfig()->DB_DATETIME_FORMAT, $fields["expiry"] ?? "", appConfig()->DB_DATETIME_TIMEZONE);
        $this->sessionExpiry = $date === false ? null : $date;
    }

    public static function currentSession(): ?Session {

        // Már van session
        if (isset(Session::$currentSession))
            return Session::$currentSession;

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

        Session::$currentSession = new Session($fields);
        return Session::$currentSession;
    }

    public static function currentUser() { 
        return Session::currentSession()?->user;
    }

    public static function isAuthenticated() { 
        return Session::currentSession() !== null;
    }

    public static function createSessionForUser(User $user) {

        // Ha van érvényes session, használjuk azt
        if (!($stmt = mysqli_prepare(appConfig()->DB_CONN, Session::queryActiveSession)) ||
            !mysqli_stmt_bind_param($stmt, "i", $user->id) ||
            !mysqli_stmt_execute($stmt)) {

            mysqli_stmt_close($stmt);
            return;
        }

        $results = mysqli_stmt_get_result($stmt);
        $fields = mysqli_fetch_assoc($results);
        mysqli_free_result($results);

        if (!empty($fields)) {
            Session::$currentSession = new Session($fields);
            Session::initialiseSessionCookie(Session::$currentSession->sessionId, Session::$currentSession->sessionExpiry);
            return;
        }

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
        Session::initialiseSessionCookie($sessId, $expiry);

        // És beállítjuk a currentSessiont, hogy a currentSession() a továbbiakban ezt adja vissza
        Session::$currentSession = new Session(null);
        Session::$currentSession->id = mysqli_insert_id(appConfig()->DB_CONN);
        Session::$currentSession->user = $user;
        Session::$currentSession->sessionId = $sessId;
        Session::$currentSession->sessionExpiry = $expiry;
    }

    private static function initialiseSessionCookie(string $sid, DateTimeImmutable $expiry) {
        setcookie(appConfig()->SESSION_COOKIE_NAME, $sid, $expiry->getTimestamp(), "/", "", false, true);
    }
} 
<?php

namespace EcoDrive\Models;

require_once "config.php";

use DateTimeImmutable;
use function EcoDrive\Environment\appConfig;

require_once appConfig()->APP_ROOT . "/models/Model.php";

class User extends Model {
    public int $id;
    public string $username;
    public string $email;
    public string $password;
    public ?string $resetToken;
    public ?DateTimeImmutable $resetTokenExpiry;

    private const findByEmailQuery = "SELECT * FROM users WHERE email LIKE ?";
    private const findByUsernameQuery = "SELECT * FROM users WHERE username LIKE ?";
    private const existsByUsernameQuery = "SELECT COUNT(*) FROM users WHERE username LIKE ?";
    private const existsByEmailQuery = "SELECT COUNT(*) FROM users WHERE email LIKE ?";

    private const createUserQuery = "INSERT INTO users(username, email, password) VALUES(?, ?, ?)";

    public function __construct($row) {
        $this->id = $row["id"] ?? -1;
        $this->username = $row["username"] ?? "";
        $this->email = $row["email"] ?? "";
        $this->password = $row["password"] ?? "";
        $this->resetToken = $row["reset_token"] ?? null;

        if (isset($row["reset_token_expiry"]))
            $this->resetTokenExpiry = DateTimeImmutable::createFromFormat(appConfig()->DB_DATETIME_FORMAT, $row["reset_token_expiry"], appConfig()->DB_DATETIME_TIMEZONE);

        else
            $this->resetTokenExpiry = null;
    }

    const FIND_BY_EMAIL = 0;
    const FIND_BY_USERNAME = 1;

    public static function find(string $name, int $mode): ?User {

        if ($mode === User::FIND_BY_USERNAME && !($stmt = mysqli_prepare(appConfig()->DB_CONN, User::findByUsernameQuery))) {
            mysqli_stmt_close($stmt);
            return null;

        } else if ($mode === User::FIND_BY_EMAIL && !($stmt = mysqli_prepare(appConfig()->DB_CONN, User::findByEmailQuery))) {
            mysqli_stmt_close($stmt);
            return null;
        }

        if (!mysqli_stmt_bind_param($stmt, "s", $name) || !mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            return null;
        }

        $results = mysqli_stmt_get_result($stmt);
        $fields = mysqli_fetch_assoc($results);

        mysqli_free_result($results);
        mysqli_stmt_close($stmt);

        if ($fields === false)
            return null;

        return new User($fields);
    }

    public static function exists(string $uname, int $mode): ?bool {
        if (!($stmt = mysqli_prepare(appConfig()->DB_CONN, $mode === User::FIND_BY_USERNAME ? User::existsByUsernameQuery : User::existsByEmailQuery)) ||
            !mysqli_stmt_bind_param($stmt, "s", $uname) ||
            !mysqli_stmt_execute($stmt)) {

            mysqli_stmt_close($stmt);
            return null;
        }

        $result = mysqli_stmt_get_result($stmt);
        $fields = mysqli_fetch_array($result);

        mysqli_stmt_close($stmt);
        mysqli_free_result($result);

        if (empty($fields))
            return null;

        return $fields[0] != 0;
    }

    public static function create(string $username, string $email, string $password): ?User {
        $password = password_hash($password, PASSWORD_ARGON2I);

        if (!($stmt = mysqli_prepare(appConfig()->DB_CONN, User::createUserQuery)) ||
            !mysqli_stmt_bind_param($stmt, "sss", $username, $email, $password) ||
            !mysqli_stmt_execute($stmt)) {

            mysqli_stmt_close($stmt);
            return null;
        }

        $u = new User(null);
        $u->id = mysqli_insert_id(appConfig()->DB_CONN);

        return $u;
    }

    public function passwordEquals($plaintextPassword) {
        return password_verify($plaintextPassword, $this->password);
    }

    public function modelEscaped(): User {
        $esc = clone $this;

        $esc->username = escapeVar($esc->username);
        $esc->password = escapeVar($esc->password);
        $esc->email = escapeVar($esc->email);

        return $esc;
    }
}
<?php

namespace EcoDrive\Models;

require_once "config.php";

use DateTimeImmutable;
use DateTimeZone;
use function EcoDrive\Environment\appConfig;

class User {
    public int $id;
    public string $username;
    public string $email;
    public string $password;
    public ?string $resetToken;
    public ?DateTimeImmutable $resetTokenExpiry;

    private const findByEmailQuery = "SELECT * FROM users WHERE email LIKE ?";
    private const findByUsernameQuery = "SELECT * FROM users WHERE username LIKE ?";

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

        if ($results === false) {
            mysqli_stmt_close($stmt);
            return null;
        }

        $fields = mysqli_fetch_assoc($results);

        mysqli_free_result($results);
        mysqli_stmt_close($stmt);

        if ($fields === false)
            return null;

        return new User($fields);
    }

    public function passwordEquals($plaintextPassword) {
        return password_verify($plaintextPassword, $this->password);
    }
}
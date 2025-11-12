<?php

namespace EcoDrive\Models;

use DateTimeImmutable;

class User {
    private $table = "users";

    public int $id;
    public string $username;
    public string $email;
    public string $password;
    public string $salt;
    public string $reset_token;
    public DateTimeImmutable $resetTokenExpiry;
}
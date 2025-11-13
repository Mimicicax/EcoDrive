<?php

namespace EcoDrive\Models;

use DateTimeImmutable;

class User {
    public int $id;
    public string $username;
    public string $email;
    public string $password;
    public string $salt;
    public string $resetToken;
    public DateTimeImmutable $resetTokenExpiry;

    public function __construct($id, $uname, $email, $pass, $salt, $resetToken, $resetTokenExpiry) {
        $this->id = $id;
        $this->username = $uname;
        $this->email = $email;
        $this->password = $pass;
        $this->salt = $salt;
        $this->resetToken = $resetToken;
        $this->resetTokenExpiry = $resetTokenExpiry;
    }
}
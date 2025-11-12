<?php

namespace EcoDrive\Endpoints;

require_once "Endpoint.php";

class AuthenticationEndpoint implements Endpoint
{
    // Megjeleníti a bejelentkezés oldalát
    public function showLogin() {
    }

    // Bejelentkezteti a felhasználót
    public function doLogin() {
        
    }

    public static function requiresAuth(): bool {
        return false;
    }
}
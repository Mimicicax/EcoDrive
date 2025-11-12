<?php

namespace EcoDrive\Endpoints;

use function EcoDrive\Environment\appConfig;

require_once "config.php";
require_once appConfig()->APP_ROOT . "/endpoints/Endpoint.php";

class Authenticator implements Endpoint
{
    // Megjeleníti a bejelentkezés oldalát
    public function showLogin() {
        return view("login", [ "title" => "Bejelentkezés" ]);
    }

    // Bejelentkezteti a felhasználót
    public function doLogin() {
        
    }

    public static function requiresAuth(): bool {
        return false;
    }
}
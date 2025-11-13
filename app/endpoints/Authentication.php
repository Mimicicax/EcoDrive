<?php

namespace EcoDrive\Endpoints;

use EcoDrive\Models\Session;
use function EcoDrive\Environment\appConfig;
use function EcoDrive\Helpers\redirect;

require_once "config.php";
require_once appConfig()->APP_ROOT . "/endpoints/Endpoint.php";
require_once appConfig()->APP_ROOT . "/models/Session.php";
require_once appConfig()->APP_ROOT . "/helpers/Redirect.php";

class Authenticator implements Endpoint
{
    // Megjeleníti a bejelentkezés oldalát
    public function showLogin() {
        if (Session::isAuthenticated())
            return redirect("home");
        
        return view("login", [ "title" => "Bejelentkezés" ]);
    }

    // Bejelentkezteti a felhasználót
    public function doLogin() {
        
    }

    public static function requiresAuth(): bool {
        return false;
    }
}
<?php

namespace EcoDrive\Endpoints;

use EcoDrive\Models\User;
use function EcoDrive\Environment\appConfig;
use function EcoDrive\Routing\route;
use EcoDrive\Helpers\RedirectType;
use EcoDrive\Models\Session;
use function EcoDrive\Helpers\redirect;

require_once "config.php";
require_once appConfig()->APP_ROOT . "/endpoints/Endpoint.php";
require_once appConfig()->APP_ROOT . "/routing.php";
require_once appConfig()->APP_ROOT . "/models/Session.php";
require_once appConfig()->APP_ROOT . "/models/User.php";

class Profile implements Endpoint
{

    public function show() {
        return view("profile", [
            "activeNavLink" => route("profile"), 
            "title" => "Profil",
            "user" => Session::currentUser()
        ]);
    }

    public static function requiresAuth(): bool {
        return true;
    }
}
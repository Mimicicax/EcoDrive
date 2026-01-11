<?php

namespace EcoDrive\Endpoints;

use EcoDrive\Models\Session;
use EcoDrive\Models\User;
use function EcoDrive\Environment\appConfig;
use function EcoDrive\Routing\route;
use function EcoDrive\Helpers\redirect;

require_once "config.php";
require_once appConfig()->APP_ROOT . "/endpoints/Endpoint.php";
require_once appConfig()->APP_ROOT . "/models/User.php";
require_once appConfig()->APP_ROOT . "/helpers/UserDataValidator.php";
require_once appConfig()->APP_ROOT . "/routing.php";
require_once appConfig()->APP_ROOT . "/Helpers/Redirect.php";

class Admin implements Endpoint
{

    public function show() {
        if (!Session::currentUser()->isAdmin)
            return redirect("/", false);
        
        return $this->showView();
    }

    public static function requiresAuth(): bool {
        return true;
    }

    public static function isAdminPermissible(): bool {
        return true;
    }

    private function showView() {
        return view("admin", [
            "title" => "Adminisztráció",
            "activeNavLink" => route("admin")
        ]);
    }
}
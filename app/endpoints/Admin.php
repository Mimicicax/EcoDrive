<?php

namespace EcoDrive\Endpoints;

use EcoDrive\Helpers\UserDataValidationError;
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
            return redirect("home");
        
        $query = trim($_GET["query"] ?? "");

        if (empty($query))
            return $this->showView([ "noQuery" => true ]);

        $queriedUser = null;

        if (\EcoDrive\Helpers\validateEmail($query, false) === false)
            $queriedUser = User::find($query, User::FIND_BY_EMAIL);

        else
            $queriedUser = User::find($query, User::FIND_BY_USERNAME);

        return $this->showView([ "queriedUser" => $queriedUser, "query" => $query ]);
    }

    public static function requiresAuth(): bool {
        return true;
    }

    public static function isAdminPermissible(): bool {
        return true;
    }

    private function showView(array $data = []) {
        return view("admin", [
            ...$data,
            "title" => "Adminisztráció",
            "activeNavLink" => route("admin")
        ]);
    }
}
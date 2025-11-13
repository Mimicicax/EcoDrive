<?php

namespace EcoDrive\Endpoints;

use EcoDrive\Models\User;
use function EcoDrive\Environment\appConfig;
use EcoDrive\Helpers\RedirectType;
use EcoDrive\Models\Session;
use function EcoDrive\Helpers\redirect;

require_once "config.php";
require_once appConfig()->APP_ROOT . "/endpoints/Endpoint.php";
require_once appConfig()->APP_ROOT . "/models/Session.php";
require_once appConfig()->APP_ROOT . "/models/User.php";
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
        if (Session::isAuthenticated())
            return redirect("home", true, RedirectType::SeeOther);

        $username = $_POST["username"] ?? "";
        $rawPass = $_POST["password"] ?? "";
        $usernameIsEmail = filter_var($username, FILTER_VALIDATE_EMAIL) && true;
        $user = User::find($username, $usernameIsEmail ? User::FIND_BY_EMAIL : User::FIND_BY_USERNAME);

        if ($user !== null && $user->passwordEquals($rawPass)) {
            Session::createSessionForUser($user);
            return redirect("home", true, RedirectType::SeeOther);
        }

        return view("login", [ "title" => "Bejelentkezés", "loginError" => true ]);
    }

    public static function requiresAuth(): bool {
        return false;
    }
}
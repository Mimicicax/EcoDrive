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
require_once appConfig()->APP_ROOT . "/helpers/UserDataValidator.php";
class Authenticator implements Endpoint
{
    private const loginError = "A felhasználónév vagy jelszó helytelen";

    // Megjeleníti a bejelentkezés oldalt
    public function showLogin() {
        if (Session::isAuthenticated())
            return redirect("home");

        return $this->loginView();
    }

    // Megjeleníti a regisztrációs oldalt
    public function showRegistration() {
        if (Session::isAuthenticated())
            return redirect("home");

        return $this->registrationView();
    }

    // Bejelentkezteti a felhasználót
    public function processLogin() {
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

        // Nincs ilyen felhasználó vagy a jelszó helytelen. A felhasználónevet visszaadjuk, hogy ne kelljen újra kitöltenie (valószínűleg a jelszó helytelen)
        return $this->loginView(["providedUsername" => $username ], [ "loginError" => Authenticator::loginError]);
    }

    // Regisztrál egy felhasználót
    public function processRegistration() {
        $username = trim($_POST["username"] ?? "");
        $email = trim($_POST["email"] ?? "");
        $pass = $_POST["password"] ?? "";
        $confirmPass = $_POST["confirmPassword"] ?? "";
        $errors = [];

        if ($e = \EcoDrive\Helpers\validateUsername($username))
            $errors["usernameError"] = $e;

        if ($e = \EcoDrive\Helpers\validateEmail($email))
            $errors["emailError"] = $e;

        if ($e = \EcoDrive\Helpers\validatePassword($pass, $confirmPass))
            $errors["passwordError"] = $e;
        
        if (!empty($errors))
            return $this->registrationView([ "providedUsername" => $username, "providedEmail" => $email ], $errors);

        // Érvényes adatok, készítünk egy fiókot és sessiont
        $newUser = User::create($username, $email, $pass);

        // Adatbázis hiba vagy versenyhelyzet ¯\_(ツ)_/¯
        if (!isset($newUser))
            return $this->registrationView();

        // Elkészült, most bejelentkezhet
        return redirect("login");
    }

    public static function requiresAuth(): bool {
        return false;
    }

    private function loginView($data = null, $errors = null) {
        if (!isset($data))
            $data = [];

        $data["title"] = "Bejelentkezés";

        return view("login", $data, $errors);
    }

    private function registrationView($data = null, $errors = null) {
        if (!isset($data))
            $data = [];

        $data["title"] = "Regisztráció";

        return view("register", $data, $errors);
    }
}
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
    private const loginError = "A felhasználónév vagy jelszó helytelen";
    private const usernameLengthError = "A felhasználónév minimum 1, maximum 50 karakterből állhat";
    private const usernameCodePointError = "Érvénytelen karakter a felhasználónévben";
    private const usernameTakenError = "A felhasználónév már foglalt";
    private const invalidEmailError = "Az email cím formátuma helytelen";
    private const emailTakenError = "Az email cím már foglalt";
    private const passwordError = "A jelszónak legalább 8 karakterből kell állnia és tartalmaznia kell legalább egy nagybetűt és számot";
    private const passwordMismatchError = "A jelszavak nem egyeznek";

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
        $username = $_POST["username"] ?? "";
        $email = $_POST["email"] ?? "";
        $pass = $_POST["password"] ?? "";
        $confirmPass = $_POST["confirmPassword"] ?? "";

        $errors = [];

        if ($e = $this->validateUsername($username))
            $errors["usernameError"] = $e;

        if ($e = $this->validateEmail($email))
            $errors["emailError"] = $e;

        if ($e = $this->validatePassword($pass, $confirmPass))
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

    private function validateUsername(string $username): bool|string {
        // Minimum 1, maximum 50 karakter
        $username = trim($username);
        
        if (strlen($username) < 1 || strlen($username) > 50)
            return Authenticator::usernameLengthError;

        // Látható Unicode, egyszerűbb emojik és zászlók
        if (preg_match("/^(\p{L}|\p{M}|\p{N}|\p{P}|\p{S}|\p{Zs}|\x{1F320}-\x{1FAFF}|(\x{1F1E6}-\x{1F1FF}){2})*$/u", $username) !== 1)
            return Authenticator::usernameCodePointError;

        // Szabadnak kell lennie
        if (User::exists($username, User::FIND_BY_USERNAME))
            return Authenticator::usernameTakenError;

        return false;
    }

    private function validateEmail(string $email): bool|string {
        // Helyes formátum
        if (!filter_var($email, FILTER_VALIDATE_EMAIL))
            return Authenticator::invalidEmailError;

        // Nem használhatja más
        if (User::exists($email, User::FIND_BY_EMAIL))
            return Authenticator::emailTakenError;

        return false;
    }

    private function validatePassword(string $pass, string $confirmPass): bool|string {
        // Minimum 8 bájt, egy nagybetű és szám. Túl hosszú jelszó nem okoz problémát
        if (strlen($pass) < 8 || preg_match("/\p{Lu}|\p{N}/u", $pass) !== 1)
            return Authenticator::passwordError;

        // Nem egyeznek a jelszavak
        if ($pass != $confirmPass)
            return Authenticator::passwordMismatchError;

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
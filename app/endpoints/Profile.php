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
require_once appConfig()->APP_ROOT . "/helpers/UserDataValidator.php";

class Profile implements Endpoint
{

    private const wrongOldPasswordError = "A megadott jelszó helytelen";

    public function show() {
        return view("profile", [
            "activeNavLink" => route("profile"), 
            "title" => "Profil",
            "user" => Session::currentUser()
        ]);
    }

    public function update() {
        parse_str(file_get_contents("php://input"), $params);
        
        $username = null;
        $email = null;
        $password = null;
        $errors = [];
        
        if (isset($params["username"])) {
            $username = trim($params["username"]);

            if ($username === Session::currentUser()->username)
                $username = null;

            else if ($e = \EcoDrive\Helpers\validateUsername($username))
                $errors["usernameError"] = $e;
        }

        if (isset($params["email"])) {
            $email = trim($params["email"]);

            if ($email === Session::currentUser()->email)
                $email = null;

            else if ($e = \EcoDrive\Helpers\validateEmail($email))
                $errors["emailError"] = $e;
        }

        if (isset($params["newPassword"])) {
            $current = $params["currentPassword"] ?? "";
            $new = $params["newPassword"] ?? "";
            $confirm = $params["confirmPassword"] ?? "";

            if ($e = \EcoDrive\Helpers\validatePassword($new, $confirm))
                $errors["newPasswordError"] = $e;
            
            if (!Session::currentUser()->passwordEquals($current))
                $errors["currentPasswordError"] = Profile::wrongOldPasswordError;
            
            $password = $params["newPassword"];
        }

        if (!empty($errors)) {
            http_response_code(400);
            return http_build_query($errors);
        }

        $user = new User(null);
        $user->id = Session::currentUser()->id;
        $user->username = $username;
        $user->email = $email;
        $user->password = $password;
        
        if (!$user->update())
            http_response_code(500);
        
        else
            http_response_code(200);
    }

    public function deleteAccount() {
        $user = Session::currentUser();
        if (!$user) {
            http_response_code(401);
            return;
        }

        $user->softDelete();
        Session::destroyCurrentSession();
        http_response_code(200);
        return redirect("home", true, RedirectType::SeeOther);
    }

    public static function requiresAuth(): bool {
        return true;
    }
}
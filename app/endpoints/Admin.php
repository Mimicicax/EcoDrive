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

        return $this->showView([ "query" => $query ], $queriedUser);
    }

    public function editUser() {
        if (!Session::currentUser()->isAdmin)
            return redirect("home");

        $userId = trim($_POST["user"] ?? "");

        if (!isset($userId) || filter_var($userId, FILTER_VALIDATE_INT) === false) {
            http_response_code(404);
            return $this->showView(errors: [ "updateFailed" => true ]);
        }

        $user = User::findById((int) $userId);

        if (!isset($user)) {
            http_response_code(404);
            return $this->showView(errors: [ "updateFailed" => true ]);
        }

        if (isset($_POST["action"]) && $_POST["action"] == "delete")
            return $this->deleteUser($user);

        return $this->updateUserInfo($user);
    }

    public static function requiresAuth(): bool {
        return true;
    }

    public static function isAdminPermissible(): bool {
        return true;
    }

    private function updateUserInfo(User $user) {
        $username = null;
        $email = null;
        $password = null;
        $errors = [];

        if (isset($_POST["username"]) && $_POST["username"] !== "") {
            $username = trim($_POST["username"]);

            if ($username === $user->username)
                $username = null;

            else if ($e = \EcoDrive\Helpers\validateUsername($username))
                $errors["usernameError"] = $e;
        }

        if (isset($_POST["email"]) && $_POST["email"] !== "") {
            $email = trim($_POST["email"]);

            if ($email === $user->email)
                $email = null;

            else if ($e = \EcoDrive\Helpers\validateEmail($email))
                $errors["emailError"] = $e;
        }

        if (isset($_POST["newPassword"]) && $_POST["newPassword"] !== "") {
            $new = $_POST["newPassword"] ?? "";
            $confirm = $_POST["confirmPassword"] ?? "";

            if ($e = \EcoDrive\Helpers\validatePassword($new, $confirm))
                $errors["newPasswordError"] = $e;

            $password = $_POST["newPassword"];
        }

        if (!empty($errors)) {
            return $this->showView([
                "providedUsername" => $_POST["username"] ?? "",
                "providedEmail" => $_POST["email"] ?? "",
                "providedNewPassword" => $_POST["newPassword"] ?? "",
                "providedConfirmPassword" => $_POST["confirmPassword"] ?? ""

            ], $user, $errors);
        }

        $queryName = isset($username) ? $username : $user->username;
        $user->username = $username;
        $user->email = $email;
        $user->password = $password;

        if (!$user->update()) {
            http_response_code(500);
            return $this->showView(queriedUser: $user, errors: [ "updateFailed" => true ]);
        }

        return redirect(to: "admin", type: \EcoDrive\Helpers\RedirectType::SeeOther, additionalData: [ "query" => $queryName ]);
    }

    private function deleteUser(User $user) {

    }

    private function showView(array $data = [], ?User $queriedUser = null, ?array $errors = null) {
        return view("admin", [
            ...$data,
            "title" => "Adminisztráció",
            "queriedUser" => $queriedUser,
            "activeNavLink" => route("admin")

        ], $errors);
    }
}
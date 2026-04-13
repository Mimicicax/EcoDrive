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
require_once appConfig()->APP_ROOT . "/helpers/Redirect.php";

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
        if (!Session::currentUser()->isAdmin) {
            http_response_code(403);
            return;
        }

        $params = null;

        if ($_SERVER['REQUEST_METHOD'] === "DELETE")
            $params = $_GET;

        else
            parse_str(file_get_contents("php://input"), $params);

        $userId = trim($params["user"] ?? "");

        if (!isset($userId) || filter_var($userId, FILTER_VALIDATE_INT) === false) {
            http_response_code(404);
            return;
        }

        $user = User::findById((int) $userId);

        if (!isset($user)) {
            http_response_code(404);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === "DELETE")
            return $this->deleteUser($user);

        return $this->updateUserInfo($user, $params);
    }

    public static function requiresAuth(): bool {
        return true;
    }

    public static function isAdminPermissible(): bool {
        return true;
    }

    private function updateUserInfo(User $user, array $params) {
        $username = null;
        $email = null;
        $password = null;
        $errors = [];

        if (isset($params["username"]) && $params["username"] !== "") {
            $username = trim($params["username"]);

            if ($username === $user->username)
                $username = null;

            else if ($e = \EcoDrive\Helpers\validateUsername($username))
                $errors["usernameError"] = $e;
        }

        if (isset($params["email"]) && $params["email"] !== "") {
            $email = trim($params["email"]);

            if ($email === $user->email)
                $email = null;

            else if ($e = \EcoDrive\Helpers\validateEmail($email))
                $errors["emailError"] = $e;
        }

        if (isset($params["newPassword"]) && $params["newPassword"] !== "") {
            $new = $params["newPassword"] ?? "";
            $confirm = $params["confirmPassword"] ?? "";

            if ($e = \EcoDrive\Helpers\validatePassword($new, $confirm))
                $errors["newPasswordError"] = $e;

            $password = $params["newPassword"];
        }

        if (!empty($errors)) {
            http_response_code(422);
            return json_encode($errors);
        }

        $user->username = $username;
        $user->email = $email;
        $user->password = $password;

        if (!$user->update()) {
            http_response_code(500);
            return;
        }

        http_response_code(200);
    }

    private function deleteUser(User $user) {
        if ($user->id === Session::currentUser()->id) {
            http_response_code(422);
            return;

        } else if (!$user->delete())
            http_response_code(500);

        else
            http_response_code(200);
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

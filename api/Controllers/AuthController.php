<?php

require_once __DIR__ . '/../../app/models/User.php';
require_once __DIR__ . '/../../app/models/Session.php';
require_once __DIR__ . '/../ApiController.php';

use EcoDrive\Models\User;
use EcoDrive\Models\Session;

class AuthController extends ApiController {
    public function login() {
        if ($this->method() !== 'POST') {
            ApiResponse::error('Method not allowed', 405);
        }

        $input = $this->getJsonInput();
        $username = $input['username'] ?? null;
        $password = $input['password'] ?? null;

        if (!$username || !$password) {
            ApiResponse::error('Missing credentials', 422);
        }

        $isEmail = filter_var($username, FILTER_VALIDATE_EMAIL);
        $findBy = $isEmail ? 'email' : 'username';

        $user = null;
        if (method_exists('User', 'find')) {
            $user = User::find($username, $isEmail ? User::FIND_BY_EMAIL : User::FIND_BY_USERNAME);
        }

        if (!$user || !method_exists($user, 'passwordEquals') || !$user->passwordEquals($password)) {
            ApiResponse::error('Invalid credentials', 401);
        }

        if (method_exists('Session', 'createSessionForUser')) {
            Session::createSessionForUser($user);
        }

        ApiResponse::success(['message' => 'Login successful', 'user' => ['id' => $user->id, 'username' => $user->username, 'email' => $user->email]]);
    }

    public function register() {
        if ($this->method() !== 'POST') {
            ApiResponse::error('Method not allowed', 405);
        }

        $input = $this->getJsonInput();
        $username = $input['username'] ?? null;
        $email = $input['email'] ?? null;
        $password = $input['password'] ?? null;

        if (!$username || !$email || !$password) {
            ApiResponse::error('Missing fields', 422);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            ApiResponse::error('Invalid email', 422);
        }

        if (!method_exists('User', 'create')) {
            ApiResponse::error('User model missing create', 500);
        }

        $user = User::create($username, $email, $password);
        if (!$user) {
            ApiResponse::error('Registration failed', 400);
        }

        ApiResponse::created(['message' => 'Registration successful', 'user' => ['id' => $user->id, 'username' => $user->username, 'email' => $user->email]]);
    }

    public function logout() {
        if ($this->method() !== 'POST') {
            ApiResponse::error('Method not allowed', 405);
        }
        $this->requireAuth();
        if (method_exists('Session', 'currentSession')) {
            $sess = Session::currentSession();
            if ($sess && method_exists($sess, 'delete')) {
                $sess->delete();
            }
        }
        ApiResponse::success(['message' => 'Logout successful']);
    }

    public function me() {
        if ($this->method() !== 'GET') {
            ApiResponse::error('Method not allowed', 405);
        }
        $this->requireAuth();
        $user = $this->currentUser();
        if (!$user) ApiResponse::error('User not found', 404);
        ApiResponse::success(['user' => ['id' => $user->id, 'username' => $user->username, 'email' => $user->email]]);
    }
}

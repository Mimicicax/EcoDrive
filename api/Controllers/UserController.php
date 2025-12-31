<?php

require_once __DIR__ . '/../../app/models/User.php';
require_once __DIR__ . '/../../app/models/Session.php';
require_once __DIR__ . '/../ApiController.php';

use EcoDrive\Models\User;

class UserController extends ApiController {
    public function index() {
        if ($this->method() !== 'GET') ApiResponse::error('Method not allowed', 405);
        $this->requireAuth();
        $this->requireAdmin();
        ApiResponse::success(['users' => []]);
    }

    public function show($id) {
        if ($this->method() !== 'GET') ApiResponse::error('Method not allowed', 405);
        $this->requireAuth();
        $current = $this->currentUser();
        if (!$current || (int)$current->id !== (int)$id) {
            ApiResponse::error('Forbidden', 403);
        }
        ApiResponse::success(['user' => ['id' => $current->id, 'username' => $current->username, 'email' => $current->email]]);
    }

    public function store() {
        if ($this->method() !== 'POST') ApiResponse::error('Method not allowed', 405);
        $input = $this->getJsonInput();
        $username = $input['username'] ?? null;
        $email = $input['email'] ?? null;
        $password = $input['password'] ?? null;
        if (!$username || !$email || !$password) ApiResponse::error('Missing fields', 422);
        $user = User::create($username, $email, $password);
        if (!$user) ApiResponse::error('Creation failed', 400);
        ApiResponse::created(['user' => ['id' => $user->id, 'username' => $user->username, 'email' => $user->email]]);
    }

    public function update($id) {
        if ($this->method() !== 'PUT') ApiResponse::error('Method not allowed', 405);
        $this->requireAuth();
        $current = $this->currentUser();
        if (!$current || (int)$current->id !== (int)$id) ApiResponse::error('Forbidden', 403);
        $input = $this->getJsonInput();
        if (isset($input['username'])) $current->username = $input['username'];
        if (isset($input['email'])) $current->email = $input['email'];
        if (isset($input['password'])) $current->password = $input['password'];
        if (method_exists($current, 'update') && $current->update()) {
            ApiResponse::success(['user' => ['id' => $current->id, 'username' => $current->username, 'email' => $current->email]]);
        }
        ApiResponse::error('Update failed', 500);
    }

    public function destroy($id) {
        if ($this->method() !== 'DELETE') ApiResponse::error('Method not allowed', 405);
        $this->requireAuth();
        $this->requireOwnResourceOrAdmin($id);
        
        $user = new User(['id' => $id]);
        if (method_exists($user, 'softDelete')) {
            $user->softDelete();
            ApiResponse::success(['message' => 'User deleted successfully']);
            return;
        }
        ApiResponse::error('Deletion failed', 500);
    }
}

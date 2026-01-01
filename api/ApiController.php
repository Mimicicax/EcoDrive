<?php

require_once __DIR__ . '/../app/models/Session.php';
require_once __DIR__ . '/ApiResponse.php';

use EcoDrive\Models\Session;

class ApiController extends ApiResponse {
    protected function getJsonInput() {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        return is_array($data) ? $data : [];
    }

    protected function method(): string {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    protected function requireAuth() {
        if (!method_exists('Session', 'isAuthenticated') || !Session::isAuthenticated()) {
            self::error('Unauthorized', 401);
        }
    }

    protected function currentUser() {
        if (method_exists('Session', 'currentUser')) {
            return Session::currentUser();
        }
        return null;
    }

    protected function isAdmin($user = null) {
        $targetUser = $user ?? $this->currentUser();
        if (!$targetUser) {
            return false;
        }
        return $targetUser->role === 'admin';
    }

    protected function requireAdmin() {
        $this->requireAuth();
        if (!$this->isAdmin()) {
            self::error('Forbidden - admin access required', 403);
        }
    }

    protected function requireOwnResourceOrAdmin($resourceOwnerId) {
        $current = $this->currentUser();
        if (!$current) {
            self::error('Unauthorized', 401);
        }
        if ((int)$current->id !== (int)$resourceOwnerId && !$this->isAdmin($current)) {
            self::error('Forbidden - admin access required', 403);
        }
    }
}

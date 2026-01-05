<?php

require_once __DIR__ . '/../app/models/Session.php';
require_once __DIR__ . '/ApiResponse.php';

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
}

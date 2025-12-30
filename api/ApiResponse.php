<?php

class ApiResponse {
    public static function send($data, int $status = 200) {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode([
            'status' => $status >= 400 ? 'error' : 'success',
            'code' => $status,
            'data' => $data,
            'timestamp' => date('c')
        ]);
        exit;
    }

    public static function success($data = [], int $status = 200) {
        self::send($data, $status);
    }

    public static function created($data = []) {
        self::send($data, 201);
    }

    public static function error(string $message, int $status = 400, $details = null) {
        $payload = ['message' => $message];
        if ($details !== null) {
            $payload['details'] = $details;
        }
        self::send($payload, $status);
    }
}

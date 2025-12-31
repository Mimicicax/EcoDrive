<?php

require_once __DIR__ . '/ApiResponse.php';

class ApiRouter {
    private static $routes = [];

    public static function get($path, $handler) { self::add('GET', $path, $handler); }
    public static function post($path, $handler) { self::add('POST', $path, $handler); }
    public static function put($path, $handler) { self::add('PUT', $path, $handler); }
    public static function delete($path, $handler) { self::add('DELETE', $path, $handler); }

    private static function add($method, $path, $handler) {
        self::$routes[$method][$path] = $handler;
    }

    public static function handle($method, $requestPath) {
        $method = strtoupper($method);
        $path = parse_url($requestPath, PHP_URL_PATH);

        if (!isset(self::$routes[$method])) {
            ApiResponse::error('Not Found', 404);
        }

        foreach (self::$routes[$method] as $route => $handler) {
            $pattern = self::routeToRegex($route);
            if (preg_match($pattern, $path, $matches)) {
                $params = [];
                foreach ($matches as $k => $v) {
                    if (!is_int($k)) $params[$k] = $v;
                }

                return self::dispatch($handler, $params);
            }
        }

        ApiResponse::error('Not Found', 404);
    }

    private static function dispatch($handler, $params) {
        list($controllerName, $method) = explode('@', $handler);
        $controllerFile = __DIR__ . '/Controllers/' . $controllerName . '.php';
        if (!file_exists($controllerFile)) {
            ApiResponse::error('Handler not found', 500);
        }
        require_once $controllerFile;

        if (!class_exists($controllerName)) {
            ApiResponse::error('Handler class missing', 500);
        }

        $controller = new $controllerName();
        if (!method_exists($controller, $method)) {
            ApiResponse::error('Handler method missing', 500);
        }

        return call_user_func_array([$controller, $method], array_values($params));
    }

    private static function routeToRegex($route) {
        $regex = preg_replace_callback('/{([a-zA-Z0-9_]+)}/', function($m){
            return '(?P<' . $m[1] . '>[^/]+)';
        }, $route);
        return '#^' . $regex . '$#';
    }
}

<?php
require_once __DIR__ . '/api/ApiRouter.php';

// CORS beállítások (Kereszt-eredetű erőforrásmegosztás)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Útvonalak (routes) regisztrálása
ApiRouter::get('/api.php/v1/auth/me', 'AuthController@me');
ApiRouter::post('/api.php/v1/auth/login', 'AuthController@login');
ApiRouter::post('/api.php/v1/auth/register', 'AuthController@register');
ApiRouter::post('/api.php/v1/auth/logout', 'AuthController@logout');

ApiRouter::get('/api.php/v1/users', 'UserController@index');
ApiRouter::get('/api.php/v1/users/{id}', 'UserController@show');
ApiRouter::post('/api.php/v1/users', 'UserController@store');
ApiRouter::put('/api.php/v1/users/{id}', 'UserController@update');
ApiRouter::delete('/api.php/v1/users/{id}', 'UserController@destroy');

ApiRouter::get('/api.php/v1/vehicles', 'VehicleController@index');
ApiRouter::get('/api.php/v1/vehicles/{licensePlate}', 'VehicleController@show');
ApiRouter::post('/api.php/v1/vehicles', 'VehicleController@store');
ApiRouter::put('/api.php/v1/vehicles/{licensePlate}', 'VehicleController@update');
ApiRouter::delete('/api.php/v1/vehicles/{licensePlate}', 'VehicleController@destroy');

// Kérés feldolgozása és irányítása
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$uri = $_SERVER['REQUEST_URI'] ?? '/';
ApiRouter::handle($method, $uri);

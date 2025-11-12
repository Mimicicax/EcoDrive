<?php

namespace EcoDrive\Routing;

use function EcoDrive\Environment\appConfig;

require_once "config.php";
require_once appConfig()->APP_ROOT."/endpoints/Authentication.php";

function & _routingTable() {
    static $routingTable = [];
    return $routingTable;
}

function endpointForPath(string $route) {
    if (!array_key_exists($route, _routingTable()))
        return null;

    return _routingTable()[$route];
}

function registerRoute(string $method, string $route, string $endpoint, string $handler) {
    if (!array_key_exists($route, _routingTable()))
        _routingTable()[$route] = [ $method => [$endpoint, $handler] ];

    else
        _routingTable()[$route][$method] =[ $endpoint, $handler];
}

function registerAppRoutes() {
    // Itt lehet regisztrálni a végpontokat

    registerRoute("GET", "/auth/login", \EcoDrive\Endpoints\AuthenticationEndpoint::class, "showLogin");
    registerRoute("POST", "/auth/login", \EcoDrive\Endpoints\AuthenticationEndpoint::class, "doLogin");
}
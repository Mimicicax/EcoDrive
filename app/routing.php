<?php

namespace EcoDrive\Routing {

use function EcoDrive\Environment\appConfig;

require_once "config.php";
require_once appConfig()->APP_ROOT."/endpoints/Authentication.php";

function registerAppRoutes() {
    // Itt lehet regisztrálni a végpontokat

    registerRoute("GET", "/auth/login", \EcoDrive\Endpoints\Authenticator::class, "showLogin");
    registerRoute("POST", "/auth/login", \EcoDrive\Endpoints\Authenticator::class, "doLogin", "login");
}

function endpointForPath(string $route) {
    if (!array_key_exists($route, Internal\routingTable()))
        return null;

    return Internal\routingTable()[$route];
}

function registerRoute(string $method, string $route, string $endpoint, string $handler, ?string $name = null) {
    if (!array_key_exists($route,Internal\routingTable()))
        Internal\routingTable()[$route] = [ $method => [$endpoint, $handler] ];

    else
        Internal\routingTable()[$route][$method] =[ $endpoint, $handler];

    if (isset($name))
        Internal\routeNameTable()[$name] = appConfig()->SERVER_ADDR . $route;
}

function route($name) {
    if (!array_key_exists($name, Internal\routeNameTable()))
        return null;

    return Internal\routeNameTable()[$name];
}

}

namespace EcoDrive\Routing\Internal {
    function & routingTable() {
        static $routingTable = [];
        return $routingTable;
    }

    function & routeNameTable() {
        static $names = [];
        return $names;
    }
}
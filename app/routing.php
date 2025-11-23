<?php

namespace EcoDrive\Routing {

use function EcoDrive\Environment\appConfig;

require_once "config.php";
require_once appConfig()->APP_ROOT."/endpoints/Authentication.php";
require_once appConfig()->APP_ROOT."/endpoints/Home.php";

function registerAppRoutes() {
    // Itt lehet regisztrálni a végpontokat

    registerRoute("GET", "/", \EcoDrive\Endpoints\Home::class, "show", "home");

    registerRoute("GET", "/auth/login", \EcoDrive\Endpoints\Authenticator::class, "showLogin");
    registerRoute("POST", "/auth/login", \EcoDrive\Endpoints\Authenticator::class, "processLogin", "login");
    registerRoute("GET", "/auth/register", \EcoDrive\Endpoints\Authenticator::class, "showRegistration");
    registerRoute("POST", "/auth/register", \EcoDrive\Endpoints\Authenticator::class, "processRegistration", "register");
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
        Internal\routeNameTable()[$name] = $route;
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
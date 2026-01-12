<?php

namespace EcoDrive\Routing {

use function EcoDrive\Environment\appConfig;

require_once "config.php";
require_once appConfig()->APP_ROOT."/endpoints/Authentication.php";
require_once appConfig()->APP_ROOT."/endpoints/Vehicles.php";
require_once appConfig()->APP_ROOT."/endpoints/Profile.php";
require_once appConfig()->APP_ROOT."/endpoints/Journal.php";
require_once appConfig()->APP_ROOT."/endpoints/Statistics.php";
require_once appConfig()->APP_ROOT."/endpoints/Admin.php";

function registerAppRoutes() {
    // Itt lehet regisztrálni a végpontokat

    registerRoute("GET", "/", \EcoDrive\Endpoints\Vehicles::class, "show", "home");

    registerRoute("GET", "/admin", \EcoDrive\Endpoints\Admin::class, "show", "admin");
    registerRoute("POST", "/admin", \EcoDrive\Endpoints\Admin::class, "editUser");

    registerRoute("GET", "/login", \EcoDrive\Endpoints\Authenticator::class, "showLogin");
    registerRoute("POST", "/login", \EcoDrive\Endpoints\Authenticator::class, "processLogin", "login");
    registerRoute("GET", "/register", \EcoDrive\Endpoints\Authenticator::class, "showRegistration");
    registerRoute("POST", "/register", \EcoDrive\Endpoints\Authenticator::class, "processRegistration", "register");
    registerRoute("GET", "/logout", \EcoDrive\Endpoints\Authenticator::class, "processLogout", "logout");

    registerRoute("GET", "/vehicles", \EcoDrive\Endpoints\Vehicles::class, "show", "vehicles");
    registerRoute("POST", "/vehicles", \EcoDrive\Endpoints\Vehicles::class, "create");
    registerRoute("PUT", "/vehicles", \EcoDrive\Endpoints\Vehicles::class, "update");
    registerRoute("DELETE", "/vehicles", \EcoDrive\Endpoints\Vehicles::class, "delete");

    registerRoute("GET", "/profile", \EcoDrive\Endpoints\Profile::class, "show", "profile");
    registerRoute("PATCH", "/profile", \EcoDrive\Endpoints\Profile::class, "update");

    registerRoute("GET", "/journal", \EcoDrive\Endpoints\Journal::class, "show", "journal");
    registerRoute("POST", "/journal", \EcoDrive\Endpoints\Journal::class, "create");
    registerRoute("DELETE", "/journal", \EcoDrive\Endpoints\Journal::class, "delete");

    registerRoute("GET", "/statistics", \EcoDrive\Endpoints\Statistics::class, "show", "statistics");
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
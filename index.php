<?php

require_once "./app/boot.php";
require_once "./app/routing.php";

$uri = parse_url($_SERVER["REQUEST_URI"]);
$endpoint = EcoDrive\Routing\endpointForPath($uri["path"]);

if (!isset($endpoint))
    http_response_code(404);

else {
    $handler = $endpoint[strtoupper($_SERVER['REQUEST_METHOD'])];

    if (!isset($endpoint))
        http_response_code(405);

    else
        (new $handler[0])->{$handler[1]}();
}
<?php

use function EcoDrive\Environment\appConfig;

require_once "./app/boot.php";
require_once "./app/routing.php";
require_once "./config.php";

// Lehetővé teszi a nézetek betöltését és változók átadását az összes végpont számára
function view(string $viewName, $data = null) {
    $path = appConfig()->VIEWS_PATH . "/" . $viewName . ".php";

    // Definiáljuk a változókat, hogy a nézetben elérhetőek legyenek. Ha string típusú, akkor elkódoljuk, hogy az
    // XSS támadások ellen védekezzünk.
    if (isset($data)) {
        foreach ($data as $varName => $value) {
            global ${$varName};

            if (gettype($value) === "string")
                ${$varName} = htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5);

            else
                ${$varName} = $value;
        }
    }

    return include $path;
}

// Kérelem kezelése

$uri = parse_url($_SERVER["REQUEST_URI"]);
$endpoint = \EcoDrive\Routing\endpointForPath($uri["path"]);

if (!isset($endpoint))
    http_response_code(404);

else {
    $handler = $endpoint[strtoupper($_SERVER['REQUEST_METHOD'])];

    if (!isset($endpoint))
        http_response_code(405);

    else
        (new $handler[0])->{$handler[1]}();
}
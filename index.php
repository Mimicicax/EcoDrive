<?php

use EcoDrive\Models\Session;
use function EcoDrive\Environment\appConfig;
use function EcoDrive\Helpers\redirect;

require_once "./config.php";
require_once appConfig()->APP_ROOT . "/boot.php";
require_once appConfig()->APP_ROOT . "/routing.php";
require_once appConfig()->APP_ROOT . "/models/Session.php";
require_once appConfig()->APP_ROOT . "/helpers/Redirect.php";

// Lehetővé teszi a nézetek betöltését és változók átadását az összes végpont számára
function view(string $viewName, $data = null, $errors = null) {
    // Definiáljuk a változókat, hogy a nézetben elérhetőek legyenek. Ha string típusú, akkor elkódoljuk, hogy az
    // XSS támadások ellen védekezzünk.
    if (isset($data)) {
        extract(array_map(function($val) {
            if (gettype($val) === "string")
                return htmlspecialchars($val, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5);
            
            else
                return $val;
        }, 
        $data));
    }

    $_pageContent = appConfig()->VIEWS_PATH . "/" . $viewName . ".php";

    return include appConfig()->VIEWS_PATH . "/layout.php";
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

    else {
        $instance = new $handler[0];

        if ($instance->requiresAuth() && !Session::isAuthenticated())
            return redirect("login");

        $instance->{$handler[1]}();
    }
}
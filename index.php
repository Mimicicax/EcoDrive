<?php

use EcoDrive\Models\Session;
use function EcoDrive\Environment\appConfig;
use function EcoDrive\Helpers\redirect;

// Fontos: ez bebootolja az alkalmazást, a többi kód és require ez alatt legyen. Egyedül a confignak nincs rá garantáltan szüksége
require_once "./boot.php";
require_once "./config.php";

require_once appConfig()->APP_ROOT . "/routing.php";
require_once appConfig()->APP_ROOT . "/models/Session.php";
require_once appConfig()->APP_ROOT . "/helpers/Redirect.php";

function escapeVar($var) {
    if (gettype($var) === "string")
        return htmlspecialchars($var, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5);

    else if (gettype($var) === "array") {
        $arr = [];

        foreach (array_keys($var) as $key)
            $arr[$key] = escapeVar($var[$key]);
        
        return $arr;

    } else
        return $var;
}

// Lehetővé teszi a nézetek betöltését és változók átadását az összes végpont számára
function view(string $viewName, $data = null, $errors = null) {
    // Definiáljuk a változókat, hogy a nézetben elérhetőek legyenek. Ha string típusú, akkor elkódoljuk, hogy az
    // XSS támadások ellen védekezzünk.
    if (isset($data)) {
        extract(array_map(function($val) {
            return escapeVar($val);
        }, 
        $data));
    }

    $_pageContent = appConfig()->VIEWS_PATH . "/" . $viewName . ".php";

    return include appConfig()->VIEWS_PATH . "/layout.php";
}

// Kiszámolja az adott asset (az assets mappában lévő dolgok) címét, hogy a kliens le tudja kérni
function asset(string $fileName) {
    return "/assets/$fileName";
}

// Kérelem kezelése

$uri = parse_url($_SERVER["REQUEST_URI"]);
$endpoint = \EcoDrive\Routing\endpointForPath($uri["path"]);

if (!isset($endpoint)) {
    http_response_code(404);
    return view("404");

} else {
    $handler = $endpoint[strtoupper($_SERVER['REQUEST_METHOD'])];

    if (!isset($endpoint))
        http_response_code(405);

    else {
        $instance = new $handler[0];

        if ($instance->requiresAuth() && !Session::isAuthenticated())
            return redirect("login");

        try {
            $instance->{$handler[1]}();

        } catch (\Throwable $ex) {
            // Valamilyen végzetes, kezeletlen hiba történt

            if (appConfig()->DEBUG_MODE)
                throw $ex;
            
            http_response_code(500);
            return view("500");
        }
    }
}
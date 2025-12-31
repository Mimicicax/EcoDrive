<?php

namespace EcoDrive\Endpoints;

use function EcoDrive\Environment\appConfig;
use function EcoDrive\Routing\route;
use EcoDrive\Models\Session;

require_once "config.php";
require_once appConfig()->APP_ROOT . "/models/Session.php";
require_once appConfig()->APP_ROOT . "/routing.php";

class Statistics implements Endpoint
{

    public function show() {
        return $this->showStats();
    }

    private function showStats() {
        return view("statistics", [
            "activeNavLink" => route("statistics"), 
            "title" => "Statisztika"
        ]);
    }

    public static function requiresAuth(): bool {
        return true;
    }
}
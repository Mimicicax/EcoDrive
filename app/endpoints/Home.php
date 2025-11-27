<?php

namespace EcoDrive\Endpoints;

use EcoDrive\Models\Session;
use function EcoDrive\Environment\appConfig;
use function EcoDrive\Helpers\redirect;

require_once "config.php";
require_once appConfig()->APP_ROOT . "/models/Session.php";

class Home implements Endpoint
{
    public function show() {
        return redirect("vehicles");
    }

    public static function requiresAuth(): bool {
        return true;
    }
}
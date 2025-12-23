<?php

namespace EcoDrive\Endpoints;

use EcoDrive\Models\Session;
use EcoDrive\Models\User;
use EcoDrive\Models\Vehicle;
use function EcoDrive\Routing\route;

require_once "config.php";

class Journal implements Endpoint
{

    public function show() {
        return $this->showRoutes();
    }

    public static function requiresAuth(): bool {
        return true;
    }

    private function showRoutes() {
        $data = [
            "title" => "Napló",
            "activeNavLink" => route("journal"),
            "userVehicles" => Vehicle::findAll(Session::currentUser())
        ];

        return view("journal", $data);
    }
}
<?php

namespace EcoDrive\Endpoints;

use function EcoDrive\Routing\route;

require_once "config.php";

class Journal implements Endpoint
{

    public function show() {
        $data = [
            "title" => "Napló",
            "activeNavLink" => route("journal")
        ];

        return view("journal", $data);
    }

    public static function requiresAuth(): bool {
        return true;
    }
}
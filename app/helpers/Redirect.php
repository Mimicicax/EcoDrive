<?php

namespace EcoDrive\Helpers;

use function EcoDrive\Environment\appConfig;
use function EcoDrive\Routing\route;

require_once "config.php";
require_once appConfig()->APP_ROOT . "/app/routing.php";

enum RedirectType {
    case Normal;
    case SeeOther;
}

function redirect(string $to, bool $interpretAsRoute = true, RedirectType $type = RedirectType::Normal) {
    $code = $type === RedirectType::Normal ? 302 : 303;
    header("Location: " . ($interpretAsRoute ? route($to) : $to), true, $code);

    return 0;
}
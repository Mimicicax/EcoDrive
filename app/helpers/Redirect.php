<?php

namespace EcoDrive\Helpers;

use function EcoDrive\Environment\appConfig;
use function EcoDrive\Routing\route;

require_once "config.php";
require_once appConfig()->APP_ROOT . "/routing.php";

enum RedirectType {
    case Normal;
    case SeeOther;
}

function redirect(string $to, bool $interpretAsRoute = true, RedirectType $type = RedirectType::Normal, ?array $additionalData = null) {
    $code = $type === RedirectType::Normal ? 302 : 303;

    header("Location: " . ($interpretAsRoute ? route($to) : $to) . (isset($additionalData) ? "?" . http_build_query($additionalData) : ""), true, $code);
    
    return 0;
}
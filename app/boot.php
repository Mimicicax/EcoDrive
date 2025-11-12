<?php

use function EcoDrive\Environment\appConfig;

require_once 'config.php';
require_once 'routing.php';

// Exception helyett használjon hibakódot
mysqli_report(MYSQLI_REPORT_ERROR);

// Beállítások betöltése, adatbázis kapcsolat létrehozása
EcoDrive\Environment\appConfig();

if (mysqli_connect_errno()) {
    http_response_code(500);
    echo "Hiba majd ezt az oldalt szépre megcsináljuk vagy nem";
    exit(0);
}

// Végpontok regisztrálása
\EcoDrive\Routing\registerAppRoutes();
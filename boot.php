<?php

use function EcoDrive\Environment\appConfig;

require_once 'config.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Beállítások betöltése, adatbázis kapcsolat létrehozása
EcoDrive\Environment\appConfig();

if (appConfig()->loadFailed()) {
    http_response_code(500);
    view("500");
    exit(0);
}

// A routingot csak itt hozzuk be, mert lehet szüksége van a configra, ami csak most lett sikeresen inicializálva
require_once appConfig()->APP_ROOT . "/routing.php";

// Végpontok regisztrálása
\EcoDrive\Routing\registerAppRoutes();
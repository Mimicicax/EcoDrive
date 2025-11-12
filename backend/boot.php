<?php

namespace EcoDrive\Environment;

require_once dirname(__FILE__).'/routing.php';

// Exception helyett használjon hibakódot
mysqli_report(MYSQLI_REPORT_ERROR);

class Config {
    public $DB_CONN;
    public $WWW_HOST;
    public function __construct() {
        $ECODRIVE_ENV = parse_ini_file(dirname(__FILE__) . "/../.env");

        $this->DB_CONN = @mysqli_connect($ECODRIVE_ENV["DB_HOST"], 
        $ECODRIVE_ENV["DB_USER"], 
        $ECODRIVE_ENV["DB_PASSWORD"], 
        $ECODRIVE_ENV["DB_NAME"],
        $ECODRIVE_ENV["DB_PORT"]);

        $this->WWW_HOST = $ECODRIVE_ENV["WWW_HOST"];
    }
}

// Ezen keresztül elérhetőek a beállítások
function appConfig() {
    static $appConfig = new Config();
    return $appConfig;
}

// Beállítások betöltése, adatbázis kapcsolat létrehozása
appConfig();

if (mysqli_connect_errno()) {
    http_response_code(500);
    echo "Hiba majd ezt az oldalt szépre megcsináljuk vagy nem";
    exit(0);
}

// Végpontok regisztrálása
\EcoDrive\Routing\registerAppRoutes();
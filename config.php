<?php

namespace EcoDrive\Environment;

class Config {
    public $DB_CONN;
    public $WWW_HOST;
    public $APP_ROOT;

    public $VIEWS_PATH;

    public function __construct() {
        $this->APP_ROOT = dirname(__FILE__) . "/app";
        $this->VIEWS_PATH = $this->APP_ROOT ."/views";
        
        $ECODRIVE_ENV = parse_ini_file($this->APP_ROOT."/../.env");

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
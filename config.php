<?php

namespace EcoDrive\Environment;

use DateTimeZone;

class Config {
    public $DB_CONN;
    public $WWW_HOST;
    public $APP_ROOT;

    public $VIEWS_PATH;

    public $SERVER_ADDR;

    public $SESSION_COOKIE_NAME;

    public $DB_DATETIME_FORMAT;
    public $DB_DATETIME_TIMEZONE;

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

        // Úgyse fogunk https-t használni
        $this->SERVER_ADDR = "http://" . $this->WWW_HOST;

        $this->SESSION_COOKIE_NAME = $ECODRIVE_ENV["SESSION_COOKIE_NAME"];

        $this->DB_DATETIME_FORMAT = $ECODRIVE_ENV["DB_DATETIME_FORMAT"];

        $this->DB_DATETIME_TIMEZONE = new DateTimeZone($ECODRIVE_ENV["DB_DATETIME_TIMEZONE"]);
    }
}

// Ezen keresztül elérhetőek a beállítások
function appConfig() {
    static $appConfig = new Config();
    return $appConfig;
}
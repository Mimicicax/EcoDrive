<?php

namespace EcoDrive\Environment;

use DateTimeZone;
use ErrorException;

class Config {
    public $DB_CONN;
    public $WWW_HOST;
    public $APP_ROOT;
    public $VIEWS_PATH;
    public $SERVER_ADDR;
    public $SESSION_COOKIE_NAME;
    public $DB_DATETIME_FORMAT;
    public $DB_DATETIME_TIMEZONE;
    public $DEBUG_MODE;

    private bool $initError;

    public function __construct() {
        // Fontos: innen még nem dobhatunk exceptiont, ezért a loadFailed()-en keresztül kell kommunikálni a hibát.
        // Ez azért van, mert hiba esetén is szükség van az APP_ROOT és VIEWS_PATH változókra, hogy a megfelelő hibaoldalt megjelenítsük.
        // Tehát ennek a függvénynek mindig sikeresen inicializálnia kell ezeket a mezőket és a loadFailed()-del jeleznie a hibát.
        
        $this->APP_ROOT = dirname(__FILE__) . "/app";
        $this->VIEWS_PATH = $this->APP_ROOT ."/views";
        $this->initError = false;

        $ECODRIVE_ENV = parse_ini_file($this->APP_ROOT."/../.env");

        if ($ECODRIVE_ENV === false) {
            $this->initError = true;
            return;
        }

        $this->WWW_HOST = $ECODRIVE_ENV["WWW_HOST"];
        $this->DEBUG_MODE = (bool) $ECODRIVE_ENV["DEBUG_MODE"];

        // Úgyse fogunk https-t használni
        $this->SERVER_ADDR = "http://" . $this->WWW_HOST;

        $this->SESSION_COOKIE_NAME = $ECODRIVE_ENV["SESSION_COOKIE_NAME"];

        $this->DB_DATETIME_FORMAT = $ECODRIVE_ENV["DB_DATETIME_FORMAT"];

        $this->DB_DATETIME_TIMEZONE = new DateTimeZone($ECODRIVE_ENV["DB_DATETIME_TIMEZONE"]);

        try  {
            $this->DB_CONN = mysqli_connect(
        $ECODRIVE_ENV["DB_HOST"], 
        $ECODRIVE_ENV["DB_USER"], 
        $ECODRIVE_ENV["DB_PASSWORD"], 
        $ECODRIVE_ENV["DB_NAME"],
            $ECODRIVE_ENV["DB_PORT"]
            );

        } catch (\mysqli_sql_exception) {
            $this->initError = true;
        }
    }

    public function loadFailed() {
        return $this->initError;
    }
}

// Ezen keresztül elérhetőek a beállítások
function appConfig() {
    static $config = null;

    if (!isset($config))
        $config = new Config();

    return $config;
}
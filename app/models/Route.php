<?php

namespace EcoDrive\Models;

require_once "config.php";

use DateTimeImmutable;
use function EcoDrive\Environment\appConfig;

require_once appConfig()->APP_ROOT . "/models/Model.php";

class Route extends Model {
    public int $id;
    public Vehicle $vehicle;
    public ?DateTimeImmutable $travelStart;
    public float $distance;
    public float $emission;
    public int $fromZip;
    public string $fromCity;
    public string $fromStreet;
    public int $toZip;
    public string $toCity;
    public string $toStreet;

    private const createRouteQuery = 
        "INSERT INTO routes (
            vehicle, 
            travel_start_time, 
            distance, 
            emission, 
            from_zip, 
            from_city, 
            from_street, 
            to_zip, 
            to_city, 
            to_street
        ) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    public static function findAll(int $year) {

    }

    public static function create(Vehicle $vehicle, 
                                  DateTimeImmutable $travelStart, 
                                  float $distance, 
                                  int $fromZip, 
                                  string $fromCity, 
                                  string $fromStreet,
                                  int $toZip, 
                                  string $toCity, 
                                  string $toStreet): ?Route {

        $stmt = mysqli_stmt_init(appConfig()->DB_CONN);

        if (!$stmt)
            return null;

        if (!mysqli_stmt_prepare($stmt, Route::createRouteQuery)) {
            mysqli_stmt_close($stmt);
            return null;     
        }

        $emission = $distance * $vehicle->co2EmissionRate;
        $date = $travelStart->format(appConfig()->DB_DATETIME_FORMAT);

        if (!mysqli_stmt_bind_param($stmt, "isddississ", 
      $vehicle->id,
     $date,
            $distance, 
            $emission,
            $fromZip, 
            $fromCity, 
            $fromStreet, 
            $toZip, 
            $toCity, 
            $toStreet)) {

            mysqli_stmt_close($stmt);
            return null;
        }

        if (!mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            return null;
        }

        mysqli_stmt_close($stmt);

        $route = new Route();

        $route->id = mysqli_insert_id(appConfig()->DB_CONN);
        $route->vehicle = $vehicle;
        $route->travelStart = $travelStart;
        $route->distance = $distance;
        $route->emission = $emission;
        $route->fromZip = $fromZip;
        $route->fromCity = $fromCity;
        $route->fromStreet = $fromStreet;
        $route->toZip = $toZip;
        $route->toCity = $toCity;
        $route->toStreet = $toStreet;

        return $route;
    }

    public function modelEscaped(): Route {
        $esc = clone $this;

        $esc->vehicle = $esc->vehicle->modelEscaped();
        $esc->fromCity = escapeVar($esc->fromCity);
        $esc->fromStreet = escapeVar($esc->fromStreet);
        $esc->toCity = escapeVar($esc->toCity);
        $esc->toStreet = escapeVar($esc->toStreet);

        return $esc;
    }
}
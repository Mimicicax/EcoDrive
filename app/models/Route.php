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

    private const findYearsQuery = "SELECT DISTINCT YEAR(routes.travel_start_time) 
        FROM routes 
        WHERE vehicle IN 
            (SELECT id FROM vehicles WHERE vehicles.user = ?)     
        ORDER BY 1 DESC";

    private const findRouteQuery = "SELECT *
        FROM routes 
        WHERE vehicle = ?
            AND YEAR(travel_start_time) = ?
        ORDER BY travel_start_time DESC";

    private const findRouteByIdQuery = "SELECT routes.*
        FROM routes
        WHERE routes.id = ?
    ";

    private const findRoutesByUserQuery = "SELECT *
        FROM routes
        WHERE routes.vehicle IN (SELECT id FROM vehicles WHERE user = ?)
            AND YEAR(travel_start_time) = ?
        ORDER BY travel_start_time DESC
    ";

    private const deleteQuery = "DELETE FROM routes WHERE id = ?";

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

    public function __construct($row = null) {
        if (!isset($row))
            return;
        
        $this->id = $row["id"] ?? -1;

        if (array_key_exists("vehicles.id", $row))
            $this->vehicle = new Vehicle($row, "vehicles");

        else {
            $this->vehicle = new Vehicle([]);
            $this->vehicle->id = $row["vehicle"];
        }

        $this->travelStart = DateTimeImmutable::createFromFormat(appConfig()->DB_DATETIME_FORMAT, $row["travel_start_time"] ?? "");

        $this->distance = $row["distance"];
        $this->emission = $row["emission"];

        $this->fromZip = $row["from_zip"];
        $this->fromCity = $row["from_city"];
        $this->fromStreet = $row["from_street"];

        $this->toZip = $row["to_zip"];
        $this->toCity = $row["to_city"];
        $this->toStreet = $row["to_street"];
    }

    public static function findAllYears(User $user) {
        if (!($stmt = mysqli_prepare(appConfig()->DB_CONN, Route::findYearsQuery)))
            return null;

        if (!mysqli_stmt_bind_param($stmt, "i", $user->id) || !mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            return null;
        }

        $result = mysqli_stmt_get_result($stmt);
        mysqli_stmt_close($stmt);

        $results = [];

        while ($row = mysqli_fetch_row($result))
            array_push($results, $row[0]);

        return $results;
    }

    public static function findAll(Vehicle $vehicle, int $year) {
        if (!($stmt = mysqli_prepare(appConfig()->DB_CONN, Route::findRouteQuery)))
            return null;

        if (!mysqli_stmt_bind_param($stmt, "ii", $vehicle->id, $year) || !mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            return null;
        }

        $result = mysqli_stmt_get_result($stmt);
        mysqli_stmt_close($stmt);

        $results = [];

        while ($row = mysqli_fetch_assoc($result)) {
            $r = new Route($row);
            $r->vehicle = $vehicle;

            array_push($results, $r);
        }

        return $results;
    }

    public static function findAllForUser(User $user, int $year) {
        if (!($stmt = mysqli_prepare(appConfig()->DB_CONN, Route::findRoutesByUserQuery)))
            return null;

        if (!mysqli_stmt_bind_param($stmt, "ii", $user->id, $year) || !mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            return null;
        }

        $result = mysqli_stmt_get_result($stmt);
        mysqli_stmt_close($stmt);

        $results = [];

        while ($row = mysqli_fetch_assoc($result)) {
            $r = new Route($row);
            $r->vehicle = Vehicle::findById($row["vehicle"]);

            array_push($results, $r);
        }

        return $results;
    }

    public static function find(int $id) {
        if (!($stmt = mysqli_prepare(appConfig()->DB_CONN, Route::findRouteByIdQuery)))
            return null;

        if (!mysqli_stmt_bind_param($stmt, "i", $id) || !mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            return null;
        }

        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);

        mysqli_stmt_close($stmt);
        
        $route = new Route($row);
        $route->vehicle = Vehicle::findById($route->vehicle->id);

        return $route;
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

    public function delete() {
        $stmt = mysqli_stmt_init(appConfig()->DB_CONN);

        if (!$stmt)
            return;

        if (!mysqli_stmt_prepare($stmt, Route::deleteQuery) || !mysqli_stmt_bind_param($stmt, "i", $this->id)) {
            mysqli_stmt_close($stmt);
            return;     
        }

        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
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
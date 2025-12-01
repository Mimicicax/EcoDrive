<?php 

namespace EcoDrive\Models;

require_once "config.php";

use Throwable;
use function EcoDrive\Environment\appConfig;

require_once appConfig()->APP_ROOT . "/models/User.php";

class Vehicle {
    public int $id;
    public User $user;
    public string $brand;
    public string $model;
    public string $licensePlate;
    public int $year;
    public float $consumption;

    private const findAllVehiclesQuery = "SELECT * FROM vehicles WHERE user=?";
    private const findVehicleQuery = "SELECT * FROM vehicles WHERE license_plate LIKE ?";
    private const findVehicleOwnerQuery = "SELECT user FROM vehicles WHERE license_plate LIKE ?";
    private const createVehicleQuery = "INSERT INTO vehicles(user, brand, model, license_plate, year, consumption) VALUES(?, ?, ?, ?, ?, ?)";
    private const existsQuery = "SELECT COUNT(*) FROM vehicles WHERE license_plate LIKE ?";
    private const deleteVehicleQuery = "DELETE FROM vehicles WHERE license_plate LIKE ?";
    private const updateVehicleQuery = "UPDATE vehicles SET brand=?, model=?, license_plate=?, year=?, consumption=? WHERE id=?";
    public const ERROR_NO_ERROR = 0;
    public const ERROR_NOT_FOUND = 1;
    public const ERROR_NOT_AUTHORISED = 2;

    public function __construct($fields) {
        $this->id = $fields["id"] ?? -1;
        $this->user = new User(["id" => $fields["user"] ?? null]);
        $this->brand = $fields["brand"] ?? "";
        $this->model = $fields["model"] ?? "";
        $this->licensePlate = $fields["license_plate"] ?? "";
        $this->year = (int) ($fields["year"] ?? -1);
        $this->consumption = (float) ($fields["consumption"] ?? -1);
    }

    public static function exists(string $licensePlate) {
        $stmt = mysqli_stmt_init(appConfig()->DB_CONN);

        if (!$stmt)
            return null;

        if (!mysqli_stmt_prepare($stmt, Vehicle::existsQuery) ||
            !mysqli_stmt_bind_param($stmt, "s", $licensePlate) ||
            !mysqli_stmt_execute($stmt)) {

            mysqli_stmt_close($stmt);
            return null;
        }

        $result = mysqli_stmt_get_result($stmt);
        $fields = mysqli_fetch_array($result);

        mysqli_stmt_close($stmt);
        mysqli_free_result($result);

        if (empty($fields))
            return null;
        
        return $fields[0] != 0;
    }

    public static function findAll(User $user) {
        $stmt = mysqli_stmt_init(appConfig()->DB_CONN);

        if (!$stmt)
            return null;
        
        if (!mysqli_stmt_prepare($stmt, Vehicle::findAllVehiclesQuery) ||
            !mysqli_stmt_bind_param($stmt, "i", $user->id) ||
            !mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            return null;
        }

        $result = mysqli_stmt_get_result($stmt);
        $array = [];

        while ($fields = mysqli_fetch_assoc($result))
            array_push($array, $fields);

        mysqli_stmt_close($stmt);
        mysqli_free_result($result);

        return $array;
    }

    public static function find(string $plate) {
        $stmt = mysqli_stmt_init(appConfig()->DB_CONN);

        if (!$stmt)
            return null;

        if (!mysqli_stmt_prepare($stmt, Vehicle::findVehicleQuery) ||
            !mysqli_stmt_bind_param($stmt, "s", $plate) ||
            !mysqli_stmt_execute($stmt)) {

            mysqli_stmt_close($stmt);
            return null;
        }

        $result = mysqli_stmt_get_result($stmt);
        $fields = mysqli_fetch_assoc($result);
        
        if (empty($fields))
            return null;

        mysqli_stmt_close($stmt);
        mysqli_free_result($result);

        return new Vehicle($fields);
    }

    public static function create(User $user, string $brand, string $model, string $licensePlate, int $year, float $consumption) {
        $licensePlate = strtoupper($licensePlate);

        $stmt = mysqli_stmt_init(appConfig()->DB_CONN);

        if (!$stmt)
            return null;

        if (!mysqli_stmt_prepare($stmt, Vehicle::createVehicleQuery) ||
            !mysqli_stmt_bind_param($stmt, "isssid", $user->id, $brand, $model, $licensePlate, $year, $consumption) ||
            !mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            return null;
        }

        mysqli_stmt_close($stmt);

        $vehicle = new Vehicle(null);

        $vehicle->id = mysqli_insert_id(appConfig()->DB_CONN);
        $vehicle->user = $user;
        $vehicle->brand = $brand;
        $vehicle->model = $model;
        $vehicle->licensePlate = $licensePlate;
        $vehicle->year = $year;
        $vehicle->consumption = $consumption;

        return $vehicle;
    }

    public static function delete(User $user, string $licensePlate) {
        $licensePlate = strtoupper($licensePlate);

        $stmt = mysqli_stmt_init(appConfig()->DB_CONN);

        if (!$stmt)
            return null;

        if (!mysqli_stmt_prepare($stmt, Vehicle::findVehicleOwnerQuery) ||
            !mysqli_stmt_bind_param($stmt, "s", $licensePlate) ||
            !mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            return null;
        }

        $result = mysqli_stmt_get_result($stmt);
        $fields = mysqli_fetch_assoc($result);
        
        mysqli_free_result($result);

        if (!isset($fields)) {
            mysqli_stmt_close($stmt);
            return Vehicle::ERROR_NOT_FOUND;

        } else if ($fields["user"] != $user->id) {
            mysqli_stmt_close($stmt);
            return Vehicle::ERROR_NOT_FOUND;
        }

        if (!mysqli_stmt_prepare($stmt, Vehicle::deleteVehicleQuery) ||
            !mysqli_stmt_bind_param($stmt, "s", $licensePlate) ||
            !mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            return null;
        }

        mysqli_stmt_close($stmt);
        return Vehicle::ERROR_NO_ERROR;
    }

    public function update() {
        $stmt = mysqli_stmt_init(appConfig()->DB_CONN);
        
        if (!$stmt)
            return null;

        if (!mysqli_stmt_prepare($stmt, Vehicle::updateVehicleQuery) ||
            !mysqli_stmt_bind_param($stmt, "sssidi", $this->brand, $this->model, $this->licensePlate, $this->year, $this->consumption, $this->id) ||
            !mysqli_stmt_execute($stmt)) {

            mysqli_stmt_close($stmt);
            return null;
        }

        mysqli_stmt_close($stmt);
        return Vehicle::ERROR_NO_ERROR;
    }
}
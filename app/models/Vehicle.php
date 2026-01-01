<?php 

namespace EcoDrive\Models;

require_once "config.php";

use DateTimeImmutable;
use function EcoDrive\Environment\appConfig;

require_once appConfig()->APP_ROOT . "/models/User.php";
require_once appConfig()->APP_ROOT . "/models/Model.php";

class Vehicle extends Model {
    public int $id;
    public User $user;
    public string $brand;
    public string $model;
    public string $licensePlate;
    public int $year;
    public float $consumption;
    public float $co2EmissionRate;
    public ?DateTimeImmutable $deletedAt;

    private const findAllVehiclesQuery = "SELECT * FROM vehicles WHERE user=? AND deleted_at IS NULL";
    private const findVehicleQuery = "SELECT * FROM vehicles WHERE license_plate LIKE ? AND deleted_at IS NULL";
    private const findVehicleByIdQuery = "SELECT * FROM vehicles WHERE id = ? AND deleted_at IS NULL";
    private const findVehicleOwnerQuery = "SELECT user FROM vehicles WHERE license_plate LIKE ? AND deleted_at IS NULL";
    private const createVehicleQuery = "INSERT INTO vehicles(user, brand, model, license_plate, year, consumption, emission) VALUES(?, ?, ?, ?, ?, ?, ?)";
    private const existsQuery = "SELECT COUNT(*) FROM vehicles WHERE license_plate LIKE ? AND deleted_at IS NULL";
    private const deleteVehicleQuery = "DELETE FROM vehicles WHERE id=?";
    private const updateVehicleQuery = "UPDATE vehicles SET brand=?, model=?, license_plate=?, year=?, consumption=?, emission=? WHERE id=?";
    public const ERROR_NO_ERROR = 0;
    public const DEFAULT_EMISSION_RATE = 108.2;     // g/km

    public function __construct($fields, $prefix = "") {
        if ($prefix !==  "")
            $prefix .= ".";

        $this->id = $fields[$prefix . "id"] ?? -1;
        $this->user = new User(["id" => $fields[$prefix . "user"] ?? null]);
        $this->brand = $fields[$prefix . "brand"] ?? "";
        $this->model = $fields[$prefix . "model"] ?? "";
        $this->licensePlate = $fields[$prefix . "license_plate"] ?? "";
        $this->year = (int) ($fields[$prefix . "year"] ?? -1);
        $this->consumption = (float) ($fields[$prefix . "consumption"] ?? -1);
        $this->co2EmissionRate = (float) ($fields[$prefix . "emission"] ?? Vehicle::DEFAULT_EMISSION_RATE);
        
        if (isset($fields[$prefix . "deleted_at"]))
            $this->deletedAt = DateTimeImmutable::createFromFormat(appConfig()->DB_DATETIME_FORMAT, $fields[$prefix . "deleted_at"], appConfig()->DB_DATETIME_TIMEZONE);
        else
            $this->deletedAt = null;
    }

    public static function exists(string $licensePlate) {
        $stmt = mysqli_stmt_init(appConfig()->DB_CONN);
        $licensePlate = strtoupper($licensePlate);

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
            array_push($array, new Vehicle($fields));

        mysqli_stmt_close($stmt);
        mysqli_free_result($result);

        return $array;
    }

    public static function find(string $plate) {
        $stmt = mysqli_stmt_init(appConfig()->DB_CONN);
        $plate = strtoupper($plate);

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

    public static function findById(int $id) {
        $stmt = mysqli_stmt_init(appConfig()->DB_CONN);

        if (!$stmt)
            return null;

        if (!mysqli_stmt_prepare($stmt, Vehicle::findVehicleByIdQuery) ||
            !mysqli_stmt_bind_param($stmt, "i", $id) ||
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

    public static function create(User $user, string $brand, string $model, string $licensePlate, int $year, float $consumption, float $emission = Vehicle::DEFAULT_EMISSION_RATE) {
        $licensePlate = strtoupper($licensePlate);

        $stmt = mysqli_stmt_init(appConfig()->DB_CONN);

        if (!$stmt)
            return null;

        if (!mysqli_stmt_prepare($stmt, Vehicle::createVehicleQuery) ||
            !mysqli_stmt_bind_param($stmt, "isssidd", $user->id, $brand, $model, $licensePlate, $year, $consumption, $emission) ||
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
        $vehicle->co2EmissionRate = $emission;

        return $vehicle;
    }

    public function delete() {
        $stmt = mysqli_stmt_init(appConfig()->DB_CONN);

        if (!$stmt)
            return null;

        if (!mysqli_stmt_prepare($stmt, Vehicle::deleteVehicleQuery) ||
            !mysqli_stmt_bind_param($stmt, "i", $this->id) ||
            !mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            return null;
        }

        mysqli_stmt_close($stmt);
        return Vehicle::ERROR_NO_ERROR;
    }

    public function softDelete() {
        $stmt = mysqli_stmt_init(appConfig()->DB_CONN);
        $query = "UPDATE vehicles SET deleted_at = NOW() WHERE id = ?";

        if (!$stmt || !mysqli_stmt_prepare($stmt, $query)) {
            mysqli_stmt_close($stmt);
            return false;
        }

        if (!mysqli_stmt_bind_param($stmt, "i", $this->id) || !mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            return false;
        }

        mysqli_stmt_close($stmt);
        $this->deletedAt = new DateTimeImmutable('now', appConfig()->DB_DATETIME_TIMEZONE);
        return true;
    }

    public function restore() {
        $stmt = mysqli_stmt_init(appConfig()->DB_CONN);
        $query = "UPDATE vehicles SET deleted_at = NULL WHERE id = ?";

        if (!$stmt || !mysqli_stmt_prepare($stmt, $query)) {
            mysqli_stmt_close($stmt);
            return false;
        }

        if (!mysqli_stmt_bind_param($stmt, "i", $this->id) || !mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            return false;
        }

        mysqli_stmt_close($stmt);
        $this->deletedAt = null;
        return true;
    }

    public function update() {
        $stmt = mysqli_stmt_init(appConfig()->DB_CONN);
                
        $this->licensePlate = strtoupper($this->licensePlate);

        if (!$stmt)
            return null;

        if (!mysqli_stmt_prepare($stmt, Vehicle::updateVehicleQuery) ||
            !mysqli_stmt_bind_param($stmt, "sssiddi", $this->brand, $this->model, $this->licensePlate, $this->year, $this->consumption, $this->co2EmissionRate, $this->id) ||
            !mysqli_stmt_execute($stmt)) {

            mysqli_stmt_close($stmt);
            return null;
        }

        mysqli_stmt_close($stmt);
        return Vehicle::ERROR_NO_ERROR;
    }

    public function name() {
        $name = $this->licensePlate  . " (" . $this->brand . " " . $this->model . ")";
        return escapeVar($name);
    }

    public function modelEscaped(): Vehicle {
        $esc = clone $this;
        
        $esc->brand = escapeVar($esc->brand);
        $esc->model = escapeVar($esc->model);
        $esc->user = $esc->user->modelEscaped();
        
        return $esc;
    }
}
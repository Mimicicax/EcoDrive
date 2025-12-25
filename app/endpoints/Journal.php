<?php

namespace EcoDrive\Endpoints;

use DateTime;
use DateTimeImmutable;
use EcoDrive\Models\Route;
use EcoDrive\Models\Session;
use EcoDrive\Models\Vehicle;

use function EcoDrive\Environment\appConfig;
use function EcoDrive\Routing\route;

require_once "config.php";
require_once appConfig()->APP_ROOT . "/models/Route.php";

class Journal implements Endpoint
{
    private const vehicleRequiredError = "A gépjármű megadása kötelező";
    private const vehicleNotFoundError = "A gépjármű nem található";
    private const startTimeRequiredError = "Az indulás megadása kötelező";
    private const startTimeInvalidError = "Az indulás formátuma érvénytelen";
    private const distanceError = "A megadott távolság érvénytelen";
    private const zipError = "A megadott irányítószám érvénytelen";
    private const cityError = "A megadott város érvénytelen";

    private const routeCreationError = "Az útvonal mentése nem sikerült";
    private const dateTimeFormat = "Y-m-d H:i";

    public function show() {
        return $this->showRoutes();
    }

    public function create() {
        $plate = $_POST["vehicle"] ?? "";
        $start = str_replace("T", " ", $_POST["travel_start"] ?? "");
        $distance = str_replace(",", ".", $_POST["distance"] ?? "");

        $fromZip = $_POST["from_zip"] ?? "";
        $fromCity = $_POST["from_city"] ?? "";
        $fromStreet = $_POST["from_street"] ?? "";

        $toZip = $_POST["to_zip"] ?? "";
        $toCity = $_POST["to_city"] ?? "";
        $toStreet = $_POST["to_street"] ?? "";
    
        $errors = [];
        $vehicle = null;

        if ($plate == "")
            $errors["plateError"] = Journal::vehicleRequiredError;

        else {
            $vehicle = Vehicle::find($plate);

            if (!isset($vehicle) || $vehicle->user->id !== Session::currentUser()->id)
                return Journal::vehicleNotFoundError;
        }

        if ($e = $this->validateTravelStart($start))
            $errors["travelError"] = $e;

        if ($e = $this->validateDistance($distance))
            $errors["distanceError"] = $e;

        if ($e = $this->validateZip($fromZip))
            $errors["fromZipError"] = $e;

        if ($e = $this->validateZip($toZip))
            $errors["toZipError"] = $e;

        if (\count($errors) != 0)
            return $this->showRoutes($errors);

        $route = Route::create($vehicle, 
            DateTimeImmutable::createFromFormat(Journal::dateTimeFormat, $start), 
            (float) $distance, 
            (int) $fromZip, 
            $fromCity, 
            $fromStreet, 
            (int) $toZip, 
            $toCity, 
            $toStreet
        );

        if (!isset($route))
            $errors["creationFailure"] = Journal::routeCreationError;

        return $this->showRoutes($errors);
    }

    public static function requiresAuth(): bool {
        return true;
    }

    private function validateTravelStart(string $datetime) {
        if ($datetime === "")
            return Journal::startTimeRequiredError;

        if (!DateTime::createFromFormat(Journal::dateTimeFormat, $datetime))
            return Journal::startTimeInvalidError;

        return false;
    }

    private function validateDistance(string $dist) {
        if (filter_var($dist, FILTER_VALIDATE_FLOAT, [ "min" => 0.0 ]) === false)
            return Journal::distanceError;

        return false;
    }

    private function validateZip(string $zip) {

        if (\strlen($zip) == 0)
            return false;

        if (\strlen($zip) != 4 || filter_var($zip, FILTER_VALIDATE_INT, [ "min" => 0, "max" => 9999 ]) === false)
            return Journal::zipError;

        return false;
    }

    private function showRoutes($errors = []) {
        $data = [
            "title" => "Napló",
            "activeNavLink" => route("journal"),
            "userVehicles" => Vehicle::findAll(Session::currentUser())
        ];

        return view("journal", $data);
    }
}
<?php

namespace EcoDrive\Endpoints;

use DateTime;
use DateTimeImmutable;
use EcoDrive\Helpers\RedirectType;
use EcoDrive\Models\Route;
use EcoDrive\Models\Session;
use EcoDrive\Models\Vehicle;

use function EcoDrive\Environment\appConfig;
use function EcoDrive\Helpers\redirect;
use function EcoDrive\Routing\route;

require_once "config.php";
require_once appConfig()->APP_ROOT . "/models/Route.php";
require_once appConfig()->APP_ROOT . "/helpers/Redirect.php";

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
    
        $data = [
            "providedVehicle" =>  $_POST["vehicle"] ?? null,
            "providedTravelStart" => $_POST["travel_start"] ?? null,
            "providedDistance" => $_POST["distance"] ?? null,
            "providedFromZip" => $_POST["from_zip"] ?? null,
            "providedFromCity" => $_POST["from_city"] ?? null,
            "providedFromStreet" => $_POST["from_street"] ?? null,
            "providedToZip" => $_POST["to_zip"] ?? null,
            "providedToCity" => $_POST["to_city"] ?? null,
            "providedToStreet" => $_POST["to_street"] ?? null
        ];

        $errors = [];
        $vehicle = null;

        if ($plate == "")
            $errors["plateError"] = Journal::vehicleRequiredError;

        else {
            $vehicle = Vehicle::find($plate);

            if (!isset($vehicle) || $vehicle->user->id !== Session::currentUser()->id)
                $errors["plateError"] = Journal::vehicleNotFoundError;
        }

        if ($e = $this->validateTravelStart($start))
            $errors["travelStartError"] = $e;

        if ($e = $this->validateDistance($distance))
            $errors["distanceError"] = $e;

        if ($e = $this->validateZip($fromZip))
            $errors["fromZipError"] = $e;

        if ($e = $this->validateZip($toZip))
            $errors["toZipError"] = $e;

        if (\count($errors) != 0)
            return $this->showRoutes($data, $errors);

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

        else {
            $data = [
                "filterYear" => $route->travelStart->format("Y"),
                "filterVehicle" => $vehicle->licensePlate
            ];
        }

        return $this->showRoutes($data, $errors);
    }

    public function delete() {
        $routeId = $_POST["route"] ?? "";

        if ($routeId == "" || !filter_var($routeId, FILTER_VALIDATE_INT, [ "options" => [ "min" => 0 ]]))
            return $this->redirectAfterDelete();

        $route = Route::find($routeId);

        if ($route->vehicle->user->id !== Session::currentUser()->id) 
            return $this->redirectAfterDelete();

        $route->delete();
        return $this->redirectAfterDelete($route);
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
        if (filter_var($dist, FILTER_VALIDATE_FLOAT, [ "options" =>[ "min_range" => 0.0 ]]) === false)
            return Journal::distanceError;

        return false;
    }

    private function validateZip(string $zip) {
        if (\strlen($zip) == 0)
            return false;

        if (preg_match("/^[0-9]{4}$/", $zip) !== 1)
            return Journal::zipError;

        return false;
    }

    private function redirectAfterDelete(?Route $deletedRoute = null) {
        $data = null;

        if (isset($deletedRoute)) {
            $data = [
                "filterYear" => $deletedRoute->travelStart->format('Y'),
                "filterVehicle" => $deletedRoute->vehicle->licensePlate
            ];
        }

        return redirect("journal", true, RedirectType::SeeOther, $data);
    }

    private function showRoutes($data = [], $errors = []) {
        $data["title"] = "Napló";
        $data["activeNavLink"] = route("journal");
        $data["userVehicles"] = Vehicle::findAll(Session::currentUser());
        $data["filterYearList"] = Route::findAllYears(Session::currentUser());

        if (!empty($data["filterYearList"])) {
            $year = $data["filterYearList"][0];
            $vehicle = $data["userVehicles"][0];

            if (isset($data["filterYear"]))
                $year = $data["filterYear"];

            else if (isset($_GET["filterYear"]) && \in_array($_GET["filterYear"], $data["filterYearList"]))
                $year = $_GET["filterYear"];

            $searchPlate = null;

            if (isset($data["filterVehicle"]))
                $searchPlate = $data["filterVehicle"];

            else if (isset($_GET["filterVehicle"]))
                $searchPlate = $_GET["filterVehicle"];

            if (isset($searchPlate)) {
                foreach ($data["userVehicles"] as $v) {
                    if ($v->licensePlate == $searchPlate) {
                        $vehicle = $v;
                        break;
                    }
                }
            }

            $data["filterYear"] = $year;
            $data["filterVehicle"] = $vehicle->licensePlate;
            $data["routeList"] = Route::findAll($vehicle, $year);
        }

        return view("journal", $data, $errors);
    }
}
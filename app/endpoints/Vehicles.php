<?php

namespace EcoDrive\Endpoints;

use EcoDrive\Models\Session;
use EcoDrive\Models\Vehicle;

use function EcoDrive\Environment\appConfig;
use function EcoDrive\Routing\route;

require_once "config.php";
require_once appConfig()->APP_ROOT . "/models/Session.php";
require_once appConfig()->APP_ROOT . "/models/Vehicle.php";

class Vehicles implements Endpoint
{
    private const brandRequiredError = "A márkanév megadása kötelező";
    private const brandFormatError = "A márkanévnek alfanumerikusnak kell lennie";
    private const modelRequiredError = "A modell megadása kötelező";
    private const modelFormatError = "A modellnek alfanumerikusnak kell lennie";
    private const plateRequiredError = "A rendszám megadása kötelező";
    private const plateFormatError = "A rendszám formátuma nem megfelelő";
    private const plateExistsError = "A rendszám már foglalt";
    private const plateVerificationError = "A rendszám ellenőrzése belső hiba miatt nem sikerült";
    private const yearRequiredError = "Az évjárat megadása kötelező";
    private const yearInvalidError = "Az évjárat érvénytelen";
    private const consumptionRequiredError = "A fogyasztás megadása kötelező";
    private const consumptionInvalidError = "A megadott fogyasztás formátuma nem megfelelő";

    private const vehicleCreationFailed = "A járművet belső hiba miatt nem sikerült rögzíteni";

    public function show() {
        return $this->showVehiclesView();
    }

    public function create() {
        $brand = trim($_POST["brand"] ?? "");
        $model = trim($_POST["model"] ?? "");
        $plate = trim($_POST["licensePlate"] ?? "");
        $year = trim($_POST["year"] ?? "");
        $consumption = trim(str_replace(',', '.', $_POST["consumption"] ?? ""));
        $errors = [];

        if ($e = $this->validateBrand($brand))
            $errors["brandError"] = $e;

        if ($e = $this->validateModel($model))
            $errors["modelError"] = $e;

        if ($e = $this->validatePlate($plate))
            $errors["licensePlateError"] = $e;

        if ($e = $this->validateYear($year))
            $errors["yearError"] = $e;

        if ($e = $this->validateConsumption($consumption))
            $errors["consumptionError"] = $e;

        $backData = [
            "providedBrand" => $brand,
            "providedModel" => $model,
            "providedLicensePlate" => $plate,
            "providedYear" => $year,
            "providedConsumption" => $consumption
        ];

        if (!empty($errors)) {
            $errors["createError"] = true;
            return $this->showVehiclesView($backData, $errors);
        }

        $created = Vehicle::create(Session::currentUser(), $brand, $model, $plate, $year, $consumption);

        if (!isset($created)) {
            $errors["internalCreationError"] = Vehicles::vehicleCreationFailed;
            return $this->showVehiclesView($backData, $errors);
        }

        return $this->showVehiclesView([ "createSuccess" => true ]);
    }

    public function update() {
        
    }

    public function delete() {
        parse_str($_SERVER['QUERY_STRING'], $params);

        if (!isset($params["licensePlate"])) {
            http_response_code(422);
            echo "{\"error\": \"" . Vehicles::plateRequiredError . "\"}";

        } else {
            $err = Vehicle::delete(Session::currentUser(), trim($params["licensePlate"]));

            if ($err === Vehicle::ERROR_NO_ERROR)
                http_response_code(200);

            else if ($err === Vehicle::ERROR_NOT_AUTHORISED)
                http_response_code(401);

            else if ($err === Vehicle::ERROR_NOT_FOUND)
                http_response_code(404);

            else if ($err === null)
                http_response_code(500);

            echo $params["licensePlate"];
        }
    }

    public static function requiresAuth(): bool {
        return true;
    }

    private function validateBrand(string $brand) {
        $result = $this->validateAlphanumeric($brand);

        if ($result === 1)
            return Vehicles::brandRequiredError;

        else if ($result === 2)
            return Vehicles::brandFormatError;

        else
            return false;
    }

    private function validateModel(string $brand) {
        $result = $this->validateAlphanumeric($brand);

        if ($result === 1)
            return Vehicles::modelRequiredError;

        else if ($result === 2)
            return Vehicles::modelFormatError;

        else
            return false;
    }

    private function validatePlate(string $plate) {        
        if (strlen($plate) === 0)
            return Vehicles::plateRequiredError;

        if (preg_match("/^[a-zA-Z]{3}-[0-9]{3}$/", $plate) === 1 ||
            preg_match("/^[a-zA-Z]{2}-[a-zA-Z]{2}-[0-9]{3}$/", $plate) === 1) {

            $exists = Vehicle::exists($plate);

            if (!isset($exists))
                return Vehicles::plateVerificationError;

            return $exists ? Vehicles::plateExistsError : false;
        }

        // TODO: eegyéni rendszámok validálása. Lehetséges formátumok:
        // Három betű + négy szám, pl. ARC0717
        // Négy betű + három szám, pl. BBKA313
        // Öt betű + két szám, pl. PECAS19
        // Hat betű + egy szám: DANIKA1

        return Vehicles::plateFormatError;
    }

    private function validateYear(string $year) {        
        if (strlen($year) === 0)
            return Vehicles::yearRequiredError;

        if (filter_var($year, FILTER_VALIDATE_INT) === false)
            return Vehicles::yearInvalidError;

        $numYear = (int) $year;

        if ($numYear < 1900 || $numYear > getdate()["year"])
            return Vehicles::yearInvalidError;

        return false;
    }

    private function validateConsumption(string $cons) {        
        if (strlen($cons) === 0)
            return Vehicles::consumptionRequiredError;

        if (filter_var($cons, FILTER_VALIDATE_FLOAT, [ "min_range" => 0.01 ]) === false)
            return Vehicles::consumptionInvalidError;

        return false;
    }

    private function validateAlphanumeric(string $str) {
        if (\strlen($str) == 0)
            return 1;

        if (preg_match("/^[a-zA-Z0-9]+$/", $str) !== 1)
            return 2;

        return 0;
    }
    
    private function showVehiclesView($data = null, $errors = null) {
        if (!isset($data))
            $data = [];

        $data["title"] = "Járműveim";
        $data["vehicleList"] = Vehicle::findAll(Session::currentUser());
        $data["activeNavLink"] = route("vehicles");
        
        return view("vehicles", $data, $errors);
    }
}
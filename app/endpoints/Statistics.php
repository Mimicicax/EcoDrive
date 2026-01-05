<?php

namespace EcoDrive\Endpoints;

use DateTimeImmutable;
use function EcoDrive\Environment\appConfig;
use function EcoDrive\Routing\route;
use EcoDrive\Models\Session;
use EcoDrive\Models\Route;

require_once "config.php";
require_once appConfig()->APP_ROOT . "/models/Session.php";
require_once appConfig()->APP_ROOT . "/models/Route.php";
require_once appConfig()->APP_ROOT . "/routing.php";
require_once appConfig()->APP_ROOT . "/helpers/LeastSquaresExtrapolator.php";

class Statistics implements Endpoint
{

    private const euAverageMonthlyCo2Emission = 383333;

    public function show() {
        return $this->showStats();
    }

    private function previousYearAggregate($previousYearRoutes, $month) {
        $yearlyEmission = 0;
        $totalDistance = 0;
        $monthlyEmission = 0;

        foreach ($previousYearRoutes as $route) {
            $yearlyEmission += $route->emission;
            $totalDistance += $route->distance;

            if ($route->travelStart->format('n') == $month)
                $monthlyEmission += $route->emission;
        }
        
        return [
            "previousYearMonthlyEmission" => $monthlyEmission,
            "previousYearlyEmission" => $yearlyEmission,
            "previousYearlyDistance" => $totalDistance
        ];
    }

    private function retrieveStats() {
        $month = date('n');
        $year = date('Y');
        $monthStartDay = (new DateTimeImmutable("$year-$month-1"))->format('z');

        $currentYearRoutes = Route::findAllForUser(Session::currentUser(), $year);
        $previousYearRoutes = Route::findAllForUser(Session::currentUser(), $year - 1);
        
        if (empty($currentYearRoutes))
            return null;

        $yearlyEmission = 0;
        $monthlyEmission = 0;
        $totalYearlyDistance = 0;
        $totalMonthlyDistance = 0;
        $previousMonthEmission = 0;
        $monthlyExtrapolator = new \EcoDrive\Helpers\Statistics\LeastSquaresExtrapolator();
        $yearlyExtrapolator = new \EcoDrive\Helpers\Statistics\LeastSquaresExtrapolator();

        $monthlyEmissionPerVehicle = [];
        $yearlyEmissionPerVehicle = [];

        foreach ($currentYearRoutes as $route) {
            $yearlyEmission += $route->emission;
            $totalYearlyDistance += $route->distance;
            $yearlyExtrapolator->feed($route->travelStart->format('z') + 1, $route->emission);

            $routeMonth = $route->travelStart->format('n');

            if (\array_key_exists($route->vehicle->id, $yearlyEmissionPerVehicle))
                $yearlyEmissionPerVehicle[$route->vehicle->id]["emission"] += $route->emission;

            else
                $yearlyEmissionPerVehicle[$route->vehicle->id] = [ "vehicle" => $route->vehicle, "emission" => $route->emission ];

            if ($routeMonth == $month) {
                $monthlyEmission += $route->emission;
                $totalMonthlyDistance += $route->distance;
                $monthlyExtrapolator->feed((float) $route->travelStart->format('z') + 1 - $monthStartDay, $route->emission);

                if (\array_key_exists($route->vehicle->id, $monthlyEmissionPerVehicle))
                    $monthlyEmissionPerVehicle[$route->vehicle->id]["emission"] += $route->emission;

                else
                    $monthlyEmissionPerVehicle[$route->vehicle->id] = [ "vehicle" => $route->vehicle, "emission" => $route->emission ];

            } else if ($month > 1 && $routeMonth == $month - 1)
                $previousMonthEmission += $route->emission;
        }

        if ($month == 1) {
            foreach ($previousYearRoutes as $prev) {
                if ($prev->travelStart->format('n') == 12)
                    $previousMonthEmission += $prev->emission;
            }
        }

        $monthlyExtrapolator->finalise();
        $yearlyExtrapolator->finalise();

        return \array_merge($this->previousYearAggregate($previousYearRoutes, $month), [
            "yearlyEmission" => $yearlyEmission,
            "monthlyEmission" => $monthlyEmission,
            "monthlyDistance" => $totalMonthlyDistance,
            "previousMonthEmission" => $previousMonthEmission,
            "perVehicleMonthlyEmissionData" => $monthlyEmissionPerVehicle,
            "perVehicleYearlyEmissionData" => $yearlyEmissionPerVehicle,
            "averageEUMonthlyCO2Emission" => Statistics::euAverageMonthlyCo2Emission,
            "yearlyDistance" => $totalYearlyDistance,
            "predictedMonthlyExtrapolator" => $monthlyExtrapolator,
            "predictedYearlyExtrapolator" => $yearlyExtrapolator
        ]);
    }

    private function showStats() {
        return view("statistics", [
            "activeNavLink" => route("statistics"), 
            "title" => "Statisztika",
            "stats" => $this->retrieveStats()
        ]);
    }

    public static function requiresAuth(): bool {
        return true;
    }
}
<?php

require_once __DIR__ . '/../../app/models/Vehicle.php';
require_once __DIR__ . '/../../app/models/User.php';
require_once __DIR__ . '/../ApiController.php';

use EcoDrive\Models\Vehicle;

class VehicleController extends ApiController {
    public function index() {
        if ($this->method() !== 'GET') ApiResponse::error('Method not allowed', 405);
        $this->requireAuth();
        
        $current = $this->currentUser();
        if (!$current) ApiResponse::error('Unauthorized', 401);
        
        $vehicles = Vehicle::findAll($current);
        if (!$vehicles) $vehicles = [];
        
        ApiResponse::success(['vehicles' => array_map(function($v) {
            return [
                'id' => $v->id,
                'brand' => $v->brand,
                'model' => $v->model,
                'licensePlate' => $v->licensePlate,
                'year' => $v->year,
                'consumption' => $v->consumption,
                'co2EmissionRate' => $v->co2EmissionRate
            ];
        }, $vehicles)]);
    }

    public function show($id) {
        if ($this->method() !== 'GET') ApiResponse::error('Method not allowed', 405);
        $this->requireAuth();
        
        $current = $this->currentUser();
        if (!$current) ApiResponse::error('Unauthorized', 401);
        
        $vehicle = Vehicle::findById((int)$id);
        if (!$vehicle || $vehicle->user->id !== $current->id) {
            ApiResponse::error('Not found or forbidden', 404);
        }
        
        ApiResponse::success(['vehicle' => [
            'id' => $vehicle->id,
            'brand' => $vehicle->brand,
            'model' => $vehicle->model,
            'licensePlate' => $vehicle->licensePlate,
            'year' => $vehicle->year,
            'consumption' => $vehicle->consumption,
            'co2EmissionRate' => $vehicle->co2EmissionRate
        ]]);
    }

    public function store() {
        if ($this->method() !== 'POST') ApiResponse::error('Method not allowed', 405);
        $this->requireAuth();
        
        $current = $this->currentUser();
        if (!$current) ApiResponse::error('Unauthorized', 401);
        
        $input = $this->getJsonInput();
        $brand = $input['brand'] ?? null;
        $model = $input['model'] ?? null;
        $licensePlate = $input['licensePlate'] ?? null;
        $year = $input['year'] ?? null;
        $consumption = $input['consumption'] ?? null;
        $co2EmissionRate = $input['co2EmissionRate'] ?? Vehicle::DEFAULT_EMISSION_RATE;
        
        if (!$brand || !$model || !$licensePlate || !$year || !$consumption) {
            ApiResponse::error('Missing required fields', 422);
        }
        
        $vehicle = Vehicle::create($current, $brand, $model, $licensePlate, (int)$year, (float)$consumption, (float)$co2EmissionRate);
        if (!$vehicle) ApiResponse::error('Creation failed', 400);
        
        ApiResponse::created(['vehicle' => [
            'id' => $vehicle->id,
            'brand' => $vehicle->brand,
            'model' => $vehicle->model,
            'licensePlate' => $vehicle->licensePlate,
            'year' => $vehicle->year,
            'consumption' => $vehicle->consumption,
            'co2EmissionRate' => $vehicle->co2EmissionRate
        ]]);
    }

    public function update($id) {
        if ($this->method() !== 'PUT') ApiResponse::error('Method not allowed', 405);
        $this->requireAuth();
        
        $current = $this->currentUser();
        if (!$current) ApiResponse::error('Unauthorized', 401);
        
        $vehicle = Vehicle::findById((int)$id);
        if (!$vehicle || $vehicle->user->id !== $current->id) {
            ApiResponse::error('Not found or forbidden', 404);
        }
        
        $input = $this->getJsonInput();
        if (isset($input['brand'])) $vehicle->brand = $input['brand'];
        if (isset($input['model'])) $vehicle->model = $input['model'];
        if (isset($input['licensePlate'])) $vehicle->licensePlate = $input['licensePlate'];
        if (isset($input['year'])) $vehicle->year = (int)$input['year'];
        if (isset($input['consumption'])) $vehicle->consumption = (float)$input['consumption'];
        if (isset($input['co2EmissionRate'])) $vehicle->co2EmissionRate = (float)$input['co2EmissionRate'];
        
        if (method_exists($vehicle, 'update') && $vehicle->update()) {
            ApiResponse::success(['vehicle' => [
                'id' => $vehicle->id,
                'brand' => $vehicle->brand,
                'model' => $vehicle->model,
                'licensePlate' => $vehicle->licensePlate,
                'year' => $vehicle->year,
                'consumption' => $vehicle->consumption,
                'co2EmissionRate' => $vehicle->co2EmissionRate
            ]]);
        }
        ApiResponse::error('Update failed', 400);
    }

    public function delete($id) {
        if ($this->method() !== 'DELETE') ApiResponse::error('Method not allowed', 405);
        $this->requireAuth();
        
        $current = $this->currentUser();
        if (!$current) ApiResponse::error('Unauthorized', 401);
        
        $vehicle = Vehicle::findById((int)$id);
        if (!$vehicle || $vehicle->user->id !== $current->id) {
            ApiResponse::error('Not found or forbidden', 404);
        }
        
        if (method_exists($vehicle, 'delete') && $vehicle->delete()) {
            ApiResponse::success(['message' => 'Vehicle deleted']);
        }
        ApiResponse::error('Delete failed', 400);
    }
}

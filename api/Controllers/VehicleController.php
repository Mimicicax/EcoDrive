<?php

require_once __DIR__ . '/../../app/models/Vehicle.php';
require_once __DIR__ . '/../../app/models/Session.php';
require_once __DIR__ . '/../ApiController.php';

use EcoDrive\Models\Vehicle;

class VehicleController extends ApiController {
    public function index() {
        if ($this->method() !== 'GET') ApiResponse::error('Method not allowed', 405);
        $this->requireAuth();
        $user = $this->currentUser();
        $vehicles = [];
        if (method_exists('Vehicle', 'findAll')) {
            $vehicles = Vehicle::findAll($user);
        }
        $out = [];
        foreach ($vehicles as $v) {
            $out[] = ['id' => $v->id, 'brand' => $v->brand, 'model' => $v->model, 'license_plate' => $v->licensePlate ?? ($v->license_plate ?? null), 'year' => $v->year, 'consumption' => $v->consumption];
        }
        ApiResponse::success(['vehicles' => $out, 'count' => count($out)]);
    }

    public function show($licensePlate) {
        if ($this->method() !== 'GET') ApiResponse::error('Method not allowed', 405);
        $this->requireAuth();
        $vehicle = method_exists('Vehicle', 'find') ? Vehicle::find($licensePlate) : null;
        if (!$vehicle) ApiResponse::error('Vehicle not found', 404);
        $user = $this->currentUser();
        if ($vehicle->user->id !== $user->id) ApiResponse::error('Forbidden', 403);
        ApiResponse::success(['vehicle' => ['id' => $vehicle->id, 'brand' => $vehicle->brand, 'model' => $vehicle->model, 'license_plate' => $vehicle->licensePlate ?? ($vehicle->license_plate ?? null), 'year' => $vehicle->year, 'consumption' => $vehicle->consumption]]);
    }

    public function store() {
        if ($this->method() !== 'POST') ApiResponse::error('Method not allowed', 405);
        $this->requireAuth();
        $input = $this->getJsonInput();
        $required = ['brand','model','license_plate','year','consumption'];
        foreach ($required as $f) if (!isset($input[$f])) ApiResponse::error('Missing field: ' . $f, 422);
        $user = $this->currentUser();
        if (!method_exists('Vehicle', 'create')) ApiResponse::error('Vehicle model missing create', 500);
        $vehicle = Vehicle::create($user, $input['brand'], $input['model'], $input['license_plate'], (int)$input['year'], (float)$input['consumption']);
        if (!$vehicle) ApiResponse::error('Creation failed', 400);
        ApiResponse::created(['vehicle' => ['id' => $vehicle->id, 'brand' => $vehicle->brand, 'model' => $vehicle->model, 'license_plate' => $vehicle->licensePlate ?? ($vehicle->license_plate ?? null), 'year' => $vehicle->year, 'consumption' => $vehicle->consumption]]);
    }

    public function update($licensePlate) {
        if ($this->method() !== 'PUT') ApiResponse::error('Method not allowed', 405);
        $this->requireAuth();
        $vehicle = method_exists('Vehicle','find') ? Vehicle::find($licensePlate) : null;
        if (!$vehicle) ApiResponse::error('Vehicle not found', 404);
        $user = $this->currentUser();
        if ($vehicle->user->id !== $user->id) ApiResponse::error('Forbidden', 403);
        $input = $this->getJsonInput();
        foreach (['brand','model','license_plate','year','consumption'] as $f) if (isset($input[$f])) $vehicle->{$f === 'license_plate' ? 'licensePlate' : $f} = $input[$f];
        if (method_exists($vehicle,'update') && $vehicle->update()) {
            ApiResponse::success(['vehicle' => ['id' => $vehicle->id, 'brand' => $vehicle->brand, 'model' => $vehicle->model, 'license_plate' => $vehicle->licensePlate ?? ($vehicle->license_plate ?? null), 'year' => $vehicle->year, 'consumption' => $vehicle->consumption]]);
        }
        ApiResponse::error('Update failed', 500);
    }

    public function destroy($licensePlate) {
        if ($this->method() !== 'DELETE') ApiResponse::error('Method not allowed', 405);
        $this->requireAuth();
        $vehicle = method_exists('Vehicle','find') ? Vehicle::find($licensePlate) : null;
        if (!$vehicle) ApiResponse::error('Vehicle not found', 404);
        $user = $this->currentUser();
        if ($vehicle->user->id !== $user->id) ApiResponse::error('Forbidden', 403);
        if (method_exists($vehicle,'delete') && $vehicle->delete()) {
            ApiResponse::success(['message' => 'Vehicle deleted']);
        }
        ApiResponse::error('Deletion failed', 500);
    }
}

<?php
/**
 * Haustor.ba - Maintenance Request REST Routes
 *
 * Endpoints:
 *   GET    /maintenance-requests
 *   GET    /maintenance-requests/@id
 *   POST   /maintenance-requests
 *   PUT    /maintenance-requests/@id
 *   DELETE /maintenance-requests/@id
 */

Flight::route('GET /maintenance-requests', function (): void {
    $filters = [
        'building_id'         => Flight::request()->query['building_id'],
        'reported_by_user_id' => Flight::request()->query['reported_by_user_id'],
        'status'              => Flight::request()->query['status'],
    ];
    $requests = Flight::maintenanceRequestService()->getAll($filters);
    Flight::json($requests, 200);
});

Flight::route('GET /maintenance-requests/@id', function ($id): void {
    $request = Flight::maintenanceRequestService()->getById($id);
    Flight::json($request, 200);
});

Flight::route('POST /maintenance-requests', function (): void {
    $data = Flight::getJsonBody();
    $created = Flight::maintenanceRequestService()->create($data);
    Flight::json($created, 201);
});

Flight::route('PUT /maintenance-requests/@id', function ($id): void {
    $data = Flight::getJsonBody();
    $updated = Flight::maintenanceRequestService()->update($id, $data);
    Flight::json($updated, 200);
});

Flight::route('DELETE /maintenance-requests/@id', function ($id): void {
    Flight::maintenanceRequestService()->delete($id);
    Flight::json(['message' => 'Maintenance request deleted successfully.'], 200);
});

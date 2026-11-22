<?php
/**
 * Haustor.ba - Building REST Routes
 *
 * Endpoints:
 *   GET    /buildings
 *   GET    /buildings/@id
 *   POST   /buildings
 *   PUT    /buildings/@id
 *   DELETE /buildings/@id
 */

Flight::route('GET /buildings', function (): void {
    $managerId = Flight::request()->query['manager_id'];
    $buildings = Flight::buildingService()->getAll($managerId);
    Flight::json($buildings, 200);
});

Flight::route('GET /buildings/@id', function ($id): void {
    $building = Flight::buildingService()->getById($id);
    Flight::json($building, 200);
});

Flight::route('POST /buildings', function (): void {
    $data = Flight::getJsonBody();
    $created = Flight::buildingService()->create($data);
    Flight::json($created, 201);
});

Flight::route('PUT /buildings/@id', function ($id): void {
    $data = Flight::getJsonBody();
    $updated = Flight::buildingService()->update($id, $data);
    Flight::json($updated, 200);
});

Flight::route('DELETE /buildings/@id', function ($id): void {
    Flight::buildingService()->delete($id);
    Flight::json(['message' => 'Building deleted successfully.'], 200);
});

<?php
/**
 * Haustor.ba - Apartment REST Routes
 *
 * Endpoints:
 *   GET    /apartments
 *   GET    /apartments/@id
 *   POST   /apartments
 *   PUT    /apartments/@id
 *   DELETE /apartments/@id
 */

Flight::route('GET /apartments', function (): void {
    $buildingId = Flight::request()->query['building_id'];
    $residentId = Flight::request()->query['resident_id'];
    $apartments = Flight::apartmentService()->getAll($buildingId, $residentId);
    Flight::json($apartments, 200);
});

Flight::route('GET /apartments/@id', function ($id): void {
    $apartment = Flight::apartmentService()->getById($id);
    Flight::json($apartment, 200);
});

Flight::route('POST /apartments', function (): void {
    $data = Flight::getJsonBody();
    $created = Flight::apartmentService()->create($data);
    Flight::json($created, 201);
});

Flight::route('PUT /apartments/@id', function ($id): void {
    $data = Flight::getJsonBody();
    $updated = Flight::apartmentService()->update($id, $data);
    Flight::json($updated, 200);
});

Flight::route('DELETE /apartments/@id', function ($id): void {
    Flight::apartmentService()->delete($id);
    Flight::json(['message' => 'Apartment deleted successfully.'], 200);
});

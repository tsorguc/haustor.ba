<?php
/**
 * Haustor.ba - Category REST Routes
 *
 * Endpoints:
 *   GET    /categories
 *   GET    /categories/@id
 *   POST   /categories
 *   PUT    /categories/@id
 *   DELETE /categories/@id
 */

Flight::route('GET /categories', function (): void {
    $categories = Flight::categoryService()->getAll();
    Flight::json($categories, 200);
});

Flight::route('GET /categories/@id', function ($id): void {
    $category = Flight::categoryService()->getById($id);
    Flight::json($category, 200);
});

Flight::route('POST /categories', function (): void {
    $data = Flight::getJsonBody();
    $created = Flight::categoryService()->create($data);
    Flight::json($created, 201);
});

Flight::route('PUT /categories/@id', function ($id): void {
    $data = Flight::getJsonBody();
    $updated = Flight::categoryService()->update($id, $data);
    Flight::json($updated, 200);
});

Flight::route('DELETE /categories/@id', function ($id): void {
    Flight::categoryService()->delete($id);
    Flight::json(['message' => 'Category deleted successfully.'], 200);
});

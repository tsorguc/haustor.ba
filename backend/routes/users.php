<?php
/**
 * Haustor.ba - User REST Routes
 *
 * Endpoints:
 *   GET    /users
 *   GET    /users/@id
 *   POST   /users
 *   PUT    /users/@id
 *   DELETE /users/@id
 */

Flight::route('GET /users', function (): void {
    $role = Flight::request()->query['role'];
    $users = Flight::userService()->getAll($role !== null ? (string) $role : null);
    Flight::json($users, 200);
});

Flight::route('GET /users/@id', function ($id): void {
    $user = Flight::userService()->getById($id);
    Flight::json($user, 200);
});

Flight::route('POST /users', function (): void {
    $data = Flight::getJsonBody();
    $created = Flight::userService()->create($data);
    Flight::json($created, 201);
});

Flight::route('PUT /users/@id', function ($id): void {
    $data = Flight::getJsonBody();
    $updated = Flight::userService()->update($id, $data);
    Flight::json($updated, 200);
});

Flight::route('DELETE /users/@id', function ($id): void {
    Flight::userService()->delete($id);
    Flight::json(['message' => 'User deleted successfully.'], 200);
});

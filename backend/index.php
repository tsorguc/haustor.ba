<?php
/**
 * Haustor.ba - FlightPHP REST API Entry Point
 *
 * Bootstraps configuration, Composer autoloading, logging and error middleware,
 * lazy service bindings, and CRUD routes for all core entities.
 */

$autoloadPath = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($autoloadPath)) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'error'  => 'Backend dependencies are not installed. Run "composer install".',
        'status' => 500,
    ]);
    exit;
}

require_once $autoloadPath;

// Load environment configuration
$configPath = __DIR__ . '/config/config.php';
if (file_exists($configPath)) {
    require_once $configPath;
} else {
    require_once __DIR__ . '/config/config.example.php';
}

require_once __DIR__ . '/middleware/LoggingMiddleware.php';
require_once __DIR__ . '/middleware/ErrorMiddleware.php';

// Configure FlightPHP error handling
Flight::set('flight.handle_errors', true);
Flight::set('flight.log_errors', false);

// Register request logging and JSON error middleware
$loggingMiddleware = new LoggingMiddleware();
$loggingMiddleware->register();

$errorMiddleware = new ErrorMiddleware($loggingMiddleware);
$errorMiddleware->register();

// Map helper for safely reading JSON or form request payloads
Flight::map('getJsonBody', function (): array {
    $rawBody = trim((string) Flight::request()->getBody());

    if ($rawBody !== '') {
        $decoded = json_decode($rawBody, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
            throw new InvalidArgumentException('Invalid JSON request body.', 400);
        }
        return $decoded;
    }

    $data = Flight::request()->data->getData();
    return is_array($data) ? $data : [];
});

// Lazily register service layer instances
Flight::register('userService', UserService::class);
Flight::register('buildingService', BuildingService::class);
Flight::register('apartmentService', ApartmentService::class);
Flight::register('categoryService', CategoryService::class);
Flight::register('maintenanceRequestService', MaintenanceRequestService::class);

// Root API health endpoint
Flight::route('GET /', function (): void {
    Flight::json([
        'name'      => 'Haustor.ba REST API',
        'status'    => 'ok',
        'endpoints' => [
            '/users',
            '/buildings',
            '/apartments',
            '/categories',
            '/maintenance-requests',
        ],
    ], 200);
});

// Load entity route definitions
require_once __DIR__ . '/routes/users.php';
require_once __DIR__ . '/routes/buildings.php';
require_once __DIR__ . '/routes/apartments.php';
require_once __DIR__ . '/routes/categories.php';
require_once __DIR__ . '/routes/maintenance_requests.php';

Flight::start();

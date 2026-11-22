<?php
/**
 * Haustor.ba - JSON Error Handling Middleware
 *
 * Logs unhandled exceptions and errors internally to `backend/logs/error.log`
 * and returns clean, standardized JSON error responses without exposing
 * database errors, SQL queries, or stack traces to clients.
 */

class ErrorMiddleware
{
    private const SAFE_CLIENT_CODES = [400, 401, 403, 404, 409];

    private string $logFile;
    private ?LoggingMiddleware $requestLogger;

    public function __construct(?LoggingMiddleware $requestLogger = null, ?string $logFile = null)
    {
        $this->requestLogger = $requestLogger;
        $this->logFile = $logFile ?? (__DIR__ . '/../logs/error.log');
    }

    /**
     * Register global error and 404 handlers with FlightPHP.
     */
    public function register(): void
    {
        if (!class_exists('Flight')) {
            return;
        }

        Flight::map('error', function (Throwable $exception): void {
            $this->handleException($exception);
        });

        Flight::map('notFound', function (): void {
            $this->handleNotFound();
        });

        Flight::map('methodNotFound', function (): void {
            $method = $this->resolveMethod();
            $uri = $this->resolveUri();

            if ($this->requestLogger !== null) {
                $this->requestLogger->logRequest($method, $uri, 405, 'METHOD_NOT_ALLOWED');
            }

            $this->sendJsonError('Method not allowed.', 405);
        });
    }

    /**
     * Handle an exception by logging it internally and emitting a safe JSON response.
     */
    public function handleException(Throwable $exception): void
    {
        $statusCode = $this->resolveStatusCode($exception);
        $message = $this->resolveSafeMessage($exception, $statusCode);
        $method = $this->resolveMethod();
        $uri = $this->resolveUri();

        $this->logError($exception, $method, $uri, $statusCode);

        if ($this->requestLogger !== null) {
            $this->requestLogger->logRequest(
                $method,
                $uri,
                $statusCode,
                $statusCode >= 500 ? 'SERVER_ERROR' : 'CLIENT_ERROR'
            );
        }

        $this->sendJsonError($message, $statusCode);
    }

    /**
     * Handle unmatched routes with a standardized 404 JSON response.
     */
    public function handleNotFound(): void
    {
        $method = $this->resolveMethod();
        $uri = $this->resolveUri();

        if ($this->requestLogger !== null) {
            $this->requestLogger->logRequest($method, $uri, 404, 'NOT_FOUND');
        }

        $this->sendJsonError('Endpoint not found.', 404);
    }

    /**
     * Determine the appropriate HTTP status code for a thrown exception.
     */
    public function resolveStatusCode(Throwable $exception): int
    {
        if ($exception instanceof InvalidArgumentException) {
            $code = (int) $exception->getCode();
            return in_array($code, self::SAFE_CLIENT_CODES, true) ? $code : 400;
        }

        if ($exception instanceof RuntimeException) {
            $code = (int) $exception->getCode();
            if (in_array($code, self::SAFE_CLIENT_CODES, true)) {
                return $code;
            }
        }

        return 500;
    }

    /**
     * Return a client-safe error message (never exposing internal/SQL errors on 500).
     */
    public function resolveSafeMessage(Throwable $exception, int $statusCode): string
    {
        if ($statusCode >= 400 && $statusCode < 500) {
            $msg = trim($exception->getMessage());
            return $msg !== '' ? $msg : 'Bad request.';
        }

        return 'Internal server error.';
    }

    /**
     * Append exception details internally to `backend/logs/error.log`.
     */
    public function logError(Throwable $exception, string $method = '', string $uri = '', int $statusCode = 500): void
    {
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0775, true);
        }

        $entry = sprintf(
            "[%s] [ERROR] %s %s | status=%d | %s: %s in %s:%d%s",
            date('Y-m-d H:i:s'),
            strtoupper($method !== '' ? $method : $this->resolveMethod()),
            $uri !== '' ? $uri : $this->resolveUri(),
            $statusCode,
            get_class($exception),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            PHP_EOL
        );

        @file_put_contents($this->logFile, $entry, FILE_APPEND | LOCK_EX);
    }

    private function sendJsonError(string $message, int $statusCode): void
    {
        $payload = [
            'error'  => $message,
            'status' => $statusCode,
        ];

        if (class_exists('Flight')) {
            Flight::response()->clearBody();
            Flight::json($payload, $statusCode);
            if (!Flight::response()->sent()) {
                Flight::response()->send();
            }
            return;
        }

        if (!headers_sent()) {
            http_response_code($statusCode);
            header('Content-Type: application/json; charset=utf-8');
        }
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    }

    private function resolveMethod(): string
    {
        if (class_exists('Flight')) {
            $request = Flight::request();
            if (!empty($request->method)) {
                return (string) $request->method;
            }
        }

        return (string) ($_SERVER['REQUEST_METHOD'] ?? 'CLI');
    }

    private function resolveUri(): string
    {
        if (class_exists('Flight')) {
            $request = Flight::request();
            if (!empty($request->url)) {
                return (string) $request->url;
            }
        }

        return (string) ($_SERVER['REQUEST_URI'] ?? '/');
    }
}

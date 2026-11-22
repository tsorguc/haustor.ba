<?php
/**
 * Haustor.ba - Request Logging Middleware
 *
 * Appends incoming API request metadata (timestamp, HTTP method, URI,
 * HTTP status code, and outcome) to `backend/logs/info.log`.
 */

class LoggingMiddleware
{
    private string $logFile;
    private bool $hasLogged = false;

    public function __construct(?string $logFile = null)
    {
        $this->logFile = $logFile ?? (__DIR__ . '/../logs/info.log');
    }

    /**
     * Register request logging hook with FlightPHP.
     */
    public function register(): void
    {
        if (!class_exists('Flight')) {
            return;
        }

        Flight::after('start', function (): void {
            if ($this->hasLogged) {
                return;
            }

            $method = $this->resolveMethod();
            $uri = $this->resolveUri();
            $statusCode = $this->resolveStatusCode();
            $outcome = $this->resolveOutcome($statusCode);

            $this->logRequest($method, $uri, $statusCode, $outcome);
        });
    }

    /**
     * Append a single formatted request log entry to info.log.
     */
    public function logRequest(string $method, string $uri, int $statusCode, ?string $outcome = null): void
    {
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0775, true);
        }

        $resolvedOutcome = ($outcome !== null && $outcome !== '')
            ? $outcome
            : $this->resolveOutcome($statusCode);

        $entry = sprintf(
            "[%s] %s %s | status=%d | outcome=%s%s",
            date('Y-m-d H:i:s'),
            strtoupper($method),
            $uri,
            $statusCode,
            $resolvedOutcome,
            PHP_EOL
        );

        @file_put_contents($this->logFile, $entry, FILE_APPEND | LOCK_EX);
        $this->hasLogged = true;
    }

    /**
     * Reset per-request guard (useful for automated tests or multi-dispatch runs).
     */
    public function reset(): void
    {
        $this->hasLogged = false;
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

    private function resolveStatusCode(): int
    {
        if (class_exists('Flight')) {
            $response = Flight::response();
            if (is_object($response) && method_exists($response, 'status')) {
                $status = (int) $response->status();
                if ($status >= 100 && $status < 600) {
                    return $status;
                }
            }
        }

        $code = http_response_code();
        return is_int($code) && $code >= 100 ? $code : 200;
    }

    private function resolveOutcome(int $statusCode): string
    {
        if ($statusCode >= 500) {
            return 'SERVER_ERROR';
        }
        if ($statusCode >= 400) {
            return 'CLIENT_ERROR';
        }
        if ($statusCode >= 300) {
            return 'REDIRECT';
        }

        return 'OK';
    }
}

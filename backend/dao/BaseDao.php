<?php
/**
 * Haustor.ba - Base Data Access Object (BaseDao)
 *
 * Establishes a shared PDO database connection using credentials from
 * `backend/config/config.php` and provides prepared-statement helper methods
 * for all entity DAO classes.
 */

require_once __DIR__ . '/../config/config.php';

class BaseDao
{
    /** @var PDO Shared or instance PDO database connection */
    protected PDO $connection;

    /** @var string Database table name associated with the child DAO */
    protected string $table;

    /**
     * Initialize BaseDao for a specific database table and establish a PDO connection.
     *
     * @param string $table Database table name
     * @throws RuntimeException If the database connection fails
     */
    public function __construct(string $table)
    {
        $this->table = $this->validateIdentifier($table);

        try {
            $host = defined('DB_HOST') ? DB_HOST : '127.0.0.1';
            $port = defined('DB_PORT') ? DB_PORT : 3306;
            $dbName = defined('DB_NAME') ? DB_NAME : 'haustor_db';
            $user = defined('DB_USER') ? DB_USER : 'root';
            $pass = defined('DB_PASS')
                ? DB_PASS
                : (defined('DB_PASSWORD') ? DB_PASSWORD : '');

            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
                $host,
                (int) $port,
                $dbName
            );

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            $this->connection = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            $this->logError('Database connection failed', $e);
            throw new RuntimeException('Greška pri povezivanju sa bazom podataka.');
        }
    }

    /**
     * Validate a SQL table or column identifier to ensure only safe characters are used.
     *
     * @param string $identifier Table or column name
     * @return string Validated identifier
     * @throws InvalidArgumentException If identifier contains invalid characters
     */
    protected function validateIdentifier(string $identifier): string
    {
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $identifier)) {
            throw new InvalidArgumentException('Neispravan naziv tabele ili kolone.');
        }
        return $identifier;
    }

    /**
     * Log database errors internally to `backend/logs/error.log` without exposing details to the client.
     *
     * @param string    $context Context message
     * @param Throwable $exception Caught exception
     * @return void
     */
    protected function logError(string $context, Throwable $exception): void
    {
        $logDir = __DIR__ . '/../logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0775, true);
        }

        $logFile = $logDir . '/error.log';
        $timestamp = date('Y-m-d H:i:s');
        $entry = sprintf(
            "[%s] [DAO ERROR] %s (Table: %s): %s in %s:%d%s",
            $timestamp,
            $context,
            $this->table ?? 'unknown',
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            PHP_EOL
        );

        @file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX);
    }

    /**
     * Prepare and execute a parameterized SQL query using PDO prepared statements.
     *
     * @param string $query  Parameterized SQL query string
     * @param array  $params Associative or positional parameters to bind
     * @return PDOStatement Executed PDOStatement instance
     */
    protected function execute(string $query, array $params = []): PDOStatement
    {
        try {
            $stmt = $this->connection->prepare($query);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            $this->logError('Query execution failed', $e);
            throw new RuntimeException('Greška pri izvršavanju upita u bazi podataka.');
        }
    }

    /**
     * Execute a parameterized SELECT query and return all matching records.
     *
     * @param string $query  Parameterized SQL SELECT query
     * @param array  $params Bound parameters for the prepared statement
     * @return array List of associative arrays representing rows
     */
    protected function query(string $query, array $params = []): array
    {
        $stmt = $this->execute($query, $params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Execute a parameterized SELECT query and return a single matching record or null.
     *
     * @param string $query  Parameterized SQL SELECT query
     * @param array  $params Bound parameters for the prepared statement
     * @return array|null Associative array representing the row, or null if not found
     */
    protected function queryUnique(string $query, array $params = []): ?array
    {
        $stmt = $this->execute($query, $params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result !== false ? $result : null;
    }

    /**
     * Snake-case alias for queryUnique().
     *
     * @param string $query  Parameterized SQL SELECT query
     * @param array  $params Bound parameters for the prepared statement
     * @return array|null
     */
    protected function query_unique(string $query, array $params = []): ?array
    {
        return $this->queryUnique($query, $params);
    }

    /**
     * Insert a new row into the entity table using a prepared statement.
     *
     * @param array $data Associative array of column => value pairs
     * @return array The inserted data including its generated primary key `id`
     */
    protected function insert(array $data): array
    {
        if (empty($data)) {
            throw new InvalidArgumentException('Podaci za unos ne mogu biti prazni.');
        }

        $columns = [];
        $placeholders = [];
        $params = [];

        foreach ($data as $column => $value) {
            $validCol = $this->validateIdentifier((string) $column);
            $columns[] = "`{$validCol}`";
            $placeholders[] = ":{$validCol}";
            $params[$validCol] = $value;
        }

        $sql = sprintf(
            'INSERT INTO `%s` (%s) VALUES (%s)',
            $this->table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );

        $this->execute($sql, $params);
        $lastId = (int) $this->connection->lastInsertId();

        if ($lastId > 0) {
            $inserted = $this->get_by_id($lastId);
            if ($inserted !== null) {
                return $inserted;
            }
            $data['id'] = $lastId;
        }

        return $data;
    }

    /**
     * Update an existing row in the entity table using a prepared statement.
     *
     * @param int|string $id        Record identifier value
     * @param array      $data      Associative array of column => value pairs to update
     * @param string     $id_column Primary key column name (defaults to 'id')
     * @return array|null The updated record from the database, or null if not found
     */
    public function update($id, array $data, string $id_column = 'id'): ?array
    {
        $validIdCol = $this->validateIdentifier($id_column);
        unset($data[$validIdCol]);

        if (empty($data)) {
            throw new InvalidArgumentException('Podaci za ažuriranje ne mogu biti prazni.');
        }

        $setClauses = [];
        $params = [];

        foreach ($data as $column => $value) {
            $validCol = $this->validateIdentifier((string) $column);
            $setClauses[] = "`{$validCol}` = :{$validCol}";
            $params[$validCol] = $value;
        }

        $params['pk_id'] = $id;

        $sql = sprintf(
            'UPDATE `%s` SET %s WHERE `%s` = :pk_id',
            $this->table,
            implode(', ', $setClauses),
            $validIdCol
        );

        $this->execute($sql, $params);

        return $this->get_by_id($id);
    }

    /**
     * Retrieve all records from the entity table.
     *
     * @return array
     */
    public function get_all(): array
    {
        $sql = sprintf('SELECT * FROM `%s` ORDER BY `id` ASC', $this->table);
        return $this->query($sql);
    }

    /**
     * Retrieve a single record by its primary key ID.
     *
     * @param int|string $id Record ID
     * @return array|null
     */
    public function get_by_id($id): ?array
    {
        $sql = sprintf('SELECT * FROM `%s` WHERE `id` = :id', $this->table);
        return $this->queryUnique($sql, ['id' => $id]);
    }

    /**
     * Add a new record to the entity table.
     *
     * @param array $data Associative array of column => value pairs
     * @return array
     */
    public function add(array $data): array
    {
        return $this->insert($data);
    }

    /**
     * Delete a record by its primary key ID using a prepared statement.
     *
     * @param int|string $id Record ID
     * @return bool True if at least one row was deleted, false otherwise
     */
    public function delete($id): bool
    {
        $sql = sprintf('DELETE FROM `%s` WHERE `id` = :id', $this->table);
        $stmt = $this->execute($sql, ['id' => $id]);
        return $stmt->rowCount() > 0;
    }
}

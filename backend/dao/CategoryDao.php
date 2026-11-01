<?php
/**
 * Haustor.ba - Category Data Access Object (CategoryDao)
 *
 * Handles all database operations for the `categories` table using PDO prepared statements.
 */

require_once __DIR__ . '/BaseDao.php';

class CategoryDao extends BaseDao
{
    public function __construct()
    {
        parent::__construct('categories');
    }

    /**
     * Create a new maintenance category record.
     *
     * @param array $data Category data (name, description, default_priority)
     * @return array Newly created category record
     */
    public function add(array $data): array
    {
        return $this->insert($data);
    }

    /**
     * Retrieve all maintenance categories ordered by ID.
     *
     * @return array List of category records
     */
    public function get_all(): array
    {
        return $this->query(
            'SELECT * FROM `categories` ORDER BY `id` ASC'
        );
    }

    /**
     * Retrieve a single maintenance category by primary key ID.
     *
     * @param int|string $id Category ID
     * @return array|null Category record or null if not found
     */
    public function get_by_id($id): ?array
    {
        return $this->queryUnique(
            'SELECT * FROM `categories` WHERE `id` = :id',
            ['id' => (int) $id]
        );
    }

    /**
     * Update an existing maintenance category by ID.
     *
     * @param int|string $id        Category ID
     * @param array      $data      Associative array of columns to update
     * @param string     $id_column Primary key column name (defaults to 'id')
     * @return array|null Updated category record or null if not found
     */
    public function update($id, array $data, string $id_column = 'id'): ?array
    {
        return parent::update((int) $id, $data, $id_column);
    }

    /**
     * Delete a maintenance category by ID.
     *
     * @param int|string $id Category ID
     * @return bool True if deleted, false otherwise
     */
    public function delete($id): bool
    {
        $stmt = $this->execute(
            'DELETE FROM `categories` WHERE `id` = :id',
            ['id' => (int) $id]
        );
        return $stmt->rowCount() > 0;
    }

    /**
     * Retrieve a maintenance category by its unique name.
     *
     * @param string $name Category name
     * @return array|null Category record or null if not found
     */
    public function get_by_name(string $name): ?array
    {
        return $this->queryUnique(
            'SELECT * FROM `categories` WHERE `name` = :name',
            ['name' => $name]
        );
    }
}

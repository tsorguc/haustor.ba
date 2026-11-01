<?php
/**
 * Haustor.ba - Building Data Access Object (BuildingDao)
 *
 * Handles all database operations for the `buildings` table using PDO prepared statements.
 */

require_once __DIR__ . '/BaseDao.php';

class BuildingDao extends BaseDao
{
    public function __construct()
    {
        parent::__construct('buildings');
    }

    /**
     * Create a new building record.
     *
     * @param array $data Building data (name, address, city, postal_code, latitude, longitude, total_floors, manager_id)
     * @return array Newly created building record
     */
    public function add(array $data): array
    {
        return $this->insert($data);
    }

    /**
     * Retrieve all buildings with manager details.
     *
     * @return array List of building records
     */
    public function get_all(): array
    {
        return $this->query(
            'SELECT b.*, u.`full_name` AS `manager_name`, u.`email` AS `manager_email`
             FROM `buildings` b
             LEFT JOIN `users` u ON b.`manager_id` = u.`id`
             ORDER BY b.`id` ASC'
        );
    }

    /**
     * Retrieve a single building by primary key ID.
     *
     * @param int|string $id Building ID
     * @return array|null Building record or null if not found
     */
    public function get_by_id($id): ?array
    {
        return $this->queryUnique(
            'SELECT b.*, u.`full_name` AS `manager_name`, u.`email` AS `manager_email`
             FROM `buildings` b
             LEFT JOIN `users` u ON b.`manager_id` = u.`id`
             WHERE b.`id` = :id',
            ['id' => (int) $id]
        );
    }

    /**
     * Update an existing building record by ID.
     *
     * @param int|string $id        Building ID
     * @param array      $data      Associative array of columns to update
     * @param string     $id_column Primary key column name (defaults to 'id')
     * @return array|null Updated building record or null if not found
     */
    public function update($id, array $data, string $id_column = 'id'): ?array
    {
        return parent::update((int) $id, $data, $id_column);
    }

    /**
     * Delete a building record by ID.
     *
     * @param int|string $id Building ID
     * @return bool True if deleted, false otherwise
     */
    public function delete($id): bool
    {
        $stmt = $this->execute(
            'DELETE FROM `buildings` WHERE `id` = :id',
            ['id' => (int) $id]
        );
        return $stmt->rowCount() > 0;
    }

    /**
     * Retrieve all buildings assigned to a specific manager.
     *
     * @param int|string $manager_id Manager user ID
     * @return array List of building records
     */
    public function get_by_manager($manager_id): array
    {
        return $this->query(
            'SELECT * FROM `buildings` WHERE `manager_id` = :manager_id ORDER BY `id` ASC',
            ['manager_id' => (int) $manager_id]
        );
    }
}

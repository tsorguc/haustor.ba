<?php
/**
 * Haustor.ba - Apartment Data Access Object (ApartmentDao)
 *
 * Handles all database operations for the `apartments` table using PDO prepared statements.
 */

require_once __DIR__ . '/BaseDao.php';

class ApartmentDao extends BaseDao
{
    public function __construct()
    {
        parent::__construct('apartments');
    }

    /**
     * Create a new apartment record.
     *
     * @param array $data Apartment data (building_id, resident_id, apartment_number, floor, area_sqm)
     * @return array Newly created apartment record
     */
    public function add(array $data): array
    {
        return $this->insert($data);
    }

    /**
     * Retrieve all apartments with building and resident details.
     *
     * @return array List of apartment records
     */
    public function get_all(): array
    {
        return $this->query(
            'SELECT a.*, b.`name` AS `building_name`, b.`address` AS `building_address`,
                    u.`full_name` AS `resident_name`, u.`email` AS `resident_email`
             FROM `apartments` a
             INNER JOIN `buildings` b ON a.`building_id` = b.`id`
             LEFT JOIN `users` u ON a.`resident_id` = u.`id`
             ORDER BY a.`building_id` ASC, a.`floor` ASC, a.`apartment_number` ASC'
        );
    }

    /**
     * Retrieve a single apartment by primary key ID.
     *
     * @param int|string $id Apartment ID
     * @return array|null Apartment record or null if not found
     */
    public function get_by_id($id): ?array
    {
        return $this->queryUnique(
            'SELECT a.*, b.`name` AS `building_name`, b.`address` AS `building_address`,
                    u.`full_name` AS `resident_name`, u.`email` AS `resident_email`
             FROM `apartments` a
             INNER JOIN `buildings` b ON a.`building_id` = b.`id`
             LEFT JOIN `users` u ON a.`resident_id` = u.`id`
             WHERE a.`id` = :id',
            ['id' => (int) $id]
        );
    }

    /**
     * Update an existing apartment record by ID.
     *
     * @param int|string $id        Apartment ID
     * @param array      $data      Associative array of columns to update
     * @param string     $id_column Primary key column name (defaults to 'id')
     * @return array|null Updated apartment record or null if not found
     */
    public function update($id, array $data, string $id_column = 'id'): ?array
    {
        return parent::update((int) $id, $data, $id_column);
    }

    /**
     * Delete an apartment record by ID.
     *
     * @param int|string $id Apartment ID
     * @return bool True if deleted, false otherwise
     */
    public function delete($id): bool
    {
        $stmt = $this->execute(
            'DELETE FROM `apartments` WHERE `id` = :id',
            ['id' => (int) $id]
        );
        return $stmt->rowCount() > 0;
    }

    /**
     * Retrieve all apartments belonging to a specific building.
     *
     * @param int|string $building_id Building ID
     * @return array List of apartment records in the building
     */
    public function get_by_building($building_id): array
    {
        return $this->query(
            'SELECT a.*, u.`full_name` AS `resident_name`, u.`email` AS `resident_email`
             FROM `apartments` a
             LEFT JOIN `users` u ON a.`resident_id` = u.`id`
             WHERE a.`building_id` = :building_id
             ORDER BY a.`floor` ASC, a.`apartment_number` ASC',
            ['building_id' => (int) $building_id]
        );
    }

    /**
     * Retrieve all apartments assigned to a specific resident user.
     *
     * @param int|string $resident_id Resident user ID
     * @return array List of apartment records assigned to the resident
     */
    public function get_by_resident($resident_id): array
    {
        return $this->query(
            'SELECT a.*, b.`name` AS `building_name`, b.`address` AS `building_address`
             FROM `apartments` a
             INNER JOIN `buildings` b ON a.`building_id` = b.`id`
             WHERE a.`resident_id` = :resident_id
             ORDER BY a.`id` ASC',
            ['resident_id' => (int) $resident_id]
        );
    }
}

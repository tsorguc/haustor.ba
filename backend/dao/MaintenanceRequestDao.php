<?php
/**
 * Haustor.ba - Maintenance Request Data Access Object (MaintenanceRequestDao)
 *
 * Handles all database operations for the `maintenance_requests` table using PDO prepared statements.
 */

require_once __DIR__ . '/BaseDao.php';

class MaintenanceRequestDao extends BaseDao
{
    public function __construct()
    {
        parent::__construct('maintenance_requests');
    }

    /**
     * Create a new maintenance request record.
     *
     * @param array $data Maintenance request data
     * @return array Newly created maintenance request record
     */
    public function add(array $data): array
    {
        return $this->insert($data);
    }

    /**
     * Retrieve all maintenance requests joined with building, apartment, reporter, and category info.
     *
     * @return array List of maintenance request records
     */
    public function get_all(): array
    {
        return $this->query(
            'SELECT mr.*,
                    b.`name` AS `building_name`,
                    b.`address` AS `building_address`,
                    a.`apartment_number`,
                    u.`full_name` AS `reported_by_name`,
                    u.`email` AS `reported_by_email`,
                    c.`name` AS `category_name`
             FROM `maintenance_requests` mr
             INNER JOIN `buildings` b ON mr.`building_id` = b.`id`
             LEFT JOIN `apartments` a ON mr.`apartment_id` = a.`id`
             INNER JOIN `users` u ON mr.`reported_by_user_id` = u.`id`
             INNER JOIN `categories` c ON mr.`category_id` = c.`id`
             ORDER BY mr.`created_at` DESC, mr.`id` DESC'
        );
    }

    /**
     * Retrieve a single maintenance request by primary key ID.
     *
     * @param int|string $id Maintenance request ID
     * @return array|null Maintenance request record or null if not found
     */
    public function get_by_id($id): ?array
    {
        return $this->queryUnique(
            'SELECT mr.*,
                    b.`name` AS `building_name`,
                    b.`address` AS `building_address`,
                    a.`apartment_number`,
                    u.`full_name` AS `reported_by_name`,
                    u.`email` AS `reported_by_email`,
                    c.`name` AS `category_name`
             FROM `maintenance_requests` mr
             INNER JOIN `buildings` b ON mr.`building_id` = b.`id`
             LEFT JOIN `apartments` a ON mr.`apartment_id` = a.`id`
             INNER JOIN `users` u ON mr.`reported_by_user_id` = u.`id`
             INNER JOIN `categories` c ON mr.`category_id` = c.`id`
             WHERE mr.`id` = :id',
            ['id' => (int) $id]
        );
    }

    /**
     * Update an existing maintenance request by ID.
     *
     * @param int|string $id        Maintenance request ID
     * @param array      $data      Associative array of columns to update
     * @param string     $id_column Primary key column name (defaults to 'id')
     * @return array|null Updated maintenance request record or null if not found
     */
    public function update($id, array $data, string $id_column = 'id'): ?array
    {
        return parent::update((int) $id, $data, $id_column);
    }

    /**
     * Delete a maintenance request by ID.
     *
     * @param int|string $id Maintenance request ID
     * @return bool True if deleted, false otherwise
     */
    public function delete($id): bool
    {
        $stmt = $this->execute(
            'DELETE FROM `maintenance_requests` WHERE `id` = :id',
            ['id' => (int) $id]
        );
        return $stmt->rowCount() > 0;
    }

    /**
     * Retrieve all maintenance requests submitted by a specific resident user.
     *
     * @param int|string $user_id Reporter user ID
     * @return array List of maintenance request records
     */
    public function get_by_user($user_id): array
    {
        return $this->query(
            'SELECT mr.*,
                    b.`name` AS `building_name`,
                    a.`apartment_number`,
                    c.`name` AS `category_name`
             FROM `maintenance_requests` mr
             INNER JOIN `buildings` b ON mr.`building_id` = b.`id`
             LEFT JOIN `apartments` a ON mr.`apartment_id` = a.`id`
             INNER JOIN `categories` c ON mr.`category_id` = c.`id`
             WHERE mr.`reported_by_user_id` = :user_id
             ORDER BY mr.`created_at` DESC, mr.`id` DESC',
            ['user_id' => (int) $user_id]
        );
    }

    /**
     * Retrieve all maintenance requests for a specific building.
     *
     * @param int|string $building_id Building ID
     * @return array List of maintenance request records
     */
    public function get_by_building($building_id): array
    {
        return $this->query(
            'SELECT mr.*,
                    a.`apartment_number`,
                    u.`full_name` AS `reported_by_name`,
                    c.`name` AS `category_name`
             FROM `maintenance_requests` mr
             LEFT JOIN `apartments` a ON mr.`apartment_id` = a.`id`
             INNER JOIN `users` u ON mr.`reported_by_user_id` = u.`id`
             INNER JOIN `categories` c ON mr.`category_id` = c.`id`
             WHERE mr.`building_id` = :building_id
             ORDER BY mr.`created_at` DESC, mr.`id` DESC',
            ['building_id' => (int) $building_id]
        );
    }

    /**
     * Retrieve all maintenance requests filtered by workflow status.
     *
     * @param string $status Status ('pending', 'in_progress', 'resolved', 'rejected')
     * @return array List of maintenance request records
     */
    public function get_by_status(string $status): array
    {
        return $this->query(
            'SELECT mr.*,
                    b.`name` AS `building_name`,
                    a.`apartment_number`,
                    u.`full_name` AS `reported_by_name`,
                    c.`name` AS `category_name`
             FROM `maintenance_requests` mr
             INNER JOIN `buildings` b ON mr.`building_id` = b.`id`
             LEFT JOIN `apartments` a ON mr.`apartment_id` = a.`id`
             INNER JOIN `users` u ON mr.`reported_by_user_id` = u.`id`
             INNER JOIN `categories` c ON mr.`category_id` = c.`id`
             WHERE mr.`status` = :status
             ORDER BY mr.`created_at` DESC, mr.`id` DESC',
            ['status' => $status]
        );
    }
}

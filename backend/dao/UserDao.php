<?php
/**
 * Haustor.ba - User Data Access Object (UserDao)
 *
 * Handles all database operations for the `users` table using PDO prepared statements.
 */

require_once __DIR__ . '/BaseDao.php';

class UserDao extends BaseDao
{
    public function __construct()
    {
        parent::__construct('users');
    }

    /**
     * Create a new user record.
     *
     * @param array $data User data (full_name, email, password_hash, phone, role)
     * @return array Newly created user record
     */
    public function add(array $data): array
    {
        return $this->insert($data);
    }

    /**
     * Retrieve all users ordered by ID.
     *
     * @return array List of user records
     */
    public function get_all(): array
    {
        return $this->query(
            'SELECT `id`, `full_name`, `email`, `phone`, `role`, `created_at`, `updated_at`
             FROM `users`
             ORDER BY `id` ASC'
        );
    }

    /**
     * Retrieve a single user by primary key ID.
     *
     * @param int|string $id User ID
     * @return array|null User record or null if not found
     */
    public function get_by_id($id): ?array
    {
        return $this->queryUnique(
            'SELECT `id`, `full_name`, `email`, `phone`, `role`, `created_at`, `updated_at`
             FROM `users`
             WHERE `id` = :id',
            ['id' => (int) $id]
        );
    }

    /**
     * Update an existing user record by ID.
     *
     * @param int|string $id        User ID
     * @param array      $data      Associative array of columns to update
     * @param string     $id_column Primary key column name (defaults to 'id')
     * @return array|null Updated user record or null if not found
     */
    public function update($id, array $data, string $id_column = 'id'): ?array
    {
        return parent::update((int) $id, $data, $id_column);
    }

    /**
     * Delete a user record by ID.
     *
     * @param int|string $id User ID
     * @return bool True if deleted, false otherwise
     */
    public function delete($id): bool
    {
        $stmt = $this->execute(
            'DELETE FROM `users` WHERE `id` = :id',
            ['id' => (int) $id]
        );
        return $stmt->rowCount() > 0;
    }

    /**
     * Retrieve a user by email address (includes password_hash for authentication).
     *
     * @param string $email User email address
     * @return array|null User record or null if not found
     */
    public function get_by_email(string $email): ?array
    {
        return $this->queryUnique(
            'SELECT * FROM `users` WHERE `email` = :email',
            ['email' => $email]
        );
    }

    /**
     * Retrieve all users filtered by role ('resident' or 'manager').
     *
     * @param string $role User role
     * @return array List of matching user records
     */
    public function get_by_role(string $role): array
    {
        return $this->query(
            'SELECT `id`, `full_name`, `email`, `phone`, `role`, `created_at`, `updated_at`
             FROM `users`
             WHERE `role` = :role
             ORDER BY `full_name` ASC',
            ['role' => $role]
        );
    }
}

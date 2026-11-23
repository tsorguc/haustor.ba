<?php
/**
 * Haustor.ba - User Service
 *
 * Encapsulates validation and business logic for user management.
 */

require_once __DIR__ . '/../dao/UserDao.php';

class UserService
{
    private const ALLOWED_ROLES = ['resident', 'manager'];

    private UserDao $dao;

    public function __construct(?UserDao $dao = null)
    {
        $this->dao = $dao ?? new UserDao();
    }

    public function getAll(?string $role = null): array
    {
        if ($role !== null && $role !== '') {
            if (!in_array($role, self::ALLOWED_ROLES, true)) {
                throw new InvalidArgumentException('Invalid role filter. Allowed values: resident, manager.', 400);
            }
            return $this->dao->get_by_role($role);
        }

        return $this->dao->get_all();
    }

    public function getById($id): array
    {
        $userId = $this->validateId($id);
        $user = $this->dao->get_by_id($userId);

        if ($user === null) {
            throw new RuntimeException('User not found.', 404);
        }

        return $user;
    }

    public function create(array $data): array
    {
        $fullName = isset($data['full_name']) ? trim((string) $data['full_name']) : '';
        if ($fullName === '' || mb_strlen($fullName) > 100) {
            throw new InvalidArgumentException('Field "full_name" is required and must be at most 100 characters.', 400);
        }

        $email = isset($data['email']) ? trim((string) $data['email']) : '';
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 150) {
            throw new InvalidArgumentException('A valid "email" address is required.', 400);
        }

        if ($this->dao->get_by_email($email) !== null) {
            throw new InvalidArgumentException('A user with this email already exists.', 400);
        }

        $passwordHash = $this->resolvePasswordHash($data, true);

        $role = isset($data['role']) && $data['role'] !== '' ? trim((string) $data['role']) : 'resident';
        if (!in_array($role, self::ALLOWED_ROLES, true)) {
            throw new InvalidArgumentException('Field "role" must be either "resident" or "manager".', 400);
        }

        $phone = null;
        if (array_key_exists('phone', $data) && $data['phone'] !== null && trim((string) $data['phone']) !== '') {
            $phone = trim((string) $data['phone']);
            if (mb_strlen($phone) > 30) {
                throw new InvalidArgumentException('Field "phone" must be at most 30 characters.', 400);
            }
        }

        return $this->dao->add([
            'full_name'     => $fullName,
            'email'         => $email,
            'password_hash' => $passwordHash,
            'phone'         => $phone,
            'role'          => $role,
        ]);
    }

    public function update($id, array $data): array
    {
        $userId = $this->validateId($id);
        $existing = $this->dao->get_by_id($userId);

        if ($existing === null) {
            throw new RuntimeException('User not found.', 404);
        }

        $payload = [];

        if (array_key_exists('full_name', $data)) {
            $fullName = trim((string) $data['full_name']);
            if ($fullName === '' || mb_strlen($fullName) > 100) {
                throw new InvalidArgumentException('Field "full_name" cannot be empty and must be at most 100 characters.', 400);
            }
            $payload['full_name'] = $fullName;
        }

        if (array_key_exists('email', $data)) {
            $email = trim((string) $data['email']);
            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 150) {
                throw new InvalidArgumentException('Field "email" must be a valid email address.', 400);
            }
            $existingByEmail = $this->dao->get_by_email($email);
            if ($existingByEmail !== null && (int) $existingByEmail['id'] !== $userId) {
                throw new InvalidArgumentException('A user with this email already exists.', 400);
            }
            $payload['email'] = $email;
        }

        if (array_key_exists('password', $data) || array_key_exists('password_hash', $data)) {
            $payload['password_hash'] = $this->resolvePasswordHash($data, false);
        }

        if (array_key_exists('phone', $data)) {
            if ($data['phone'] === null || trim((string) $data['phone']) === '') {
                $payload['phone'] = null;
            } else {
                $phone = trim((string) $data['phone']);
                if (mb_strlen($phone) > 30) {
                    throw new InvalidArgumentException('Field "phone" must be at most 30 characters.', 400);
                }
                $payload['phone'] = $phone;
            }
        }

        if (array_key_exists('role', $data)) {
            $role = trim((string) $data['role']);
            if (!in_array($role, self::ALLOWED_ROLES, true)) {
                throw new InvalidArgumentException('Field "role" must be either "resident" or "manager".', 400);
            }
            $payload['role'] = $role;
        }

        if (empty($payload)) {
            throw new InvalidArgumentException('No valid fields provided for update.', 400);
        }

        $updated = $this->dao->update($userId, $payload);
        if ($updated === null) {
            throw new RuntimeException('User not found.', 404);
        }

        return $updated;
    }

    public function delete($id): bool
    {
        $userId = $this->validateId($id);

        if (!$this->dao->delete($userId)) {
            throw new RuntimeException('User not found.', 404);
        }

        return true;
    }

    private function resolvePasswordHash(array $data, bool $required): string
    {
        if (array_key_exists('password', $data) && $data['password'] !== null) {
            $password = (string) $data['password'];
            if (strlen($password) < 6) {
                throw new InvalidArgumentException('Field "password" must be at least 6 characters long.', 400);
            }
            return password_hash($password, PASSWORD_BCRYPT);
        }

        if (array_key_exists('password_hash', $data) && is_string($data['password_hash']) && trim($data['password_hash']) !== '') {
            return trim($data['password_hash']);
        }

        if ($required) {
            throw new InvalidArgumentException('Field "password" is required and must be at least 6 characters long.', 400);
        }

        throw new InvalidArgumentException('Field "password" cannot be empty.', 400);
    }

    private function validateId($id): int
    {
        if (!is_numeric($id) || (int) $id <= 0 || (string) (int) $id !== (string) $id) {
            throw new InvalidArgumentException('A valid positive numeric user ID is required.', 400);
        }

        return (int) $id;
    }
}

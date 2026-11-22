<?php
/**
 * Haustor.ba - Category Service
 *
 * Encapsulates validation and business logic for maintenance categories.
 */

require_once __DIR__ . '/../dao/CategoryDao.php';

class CategoryService
{
    private const ALLOWED_PRIORITIES = ['low', 'medium', 'high', 'urgent'];

    private CategoryDao $dao;

    public function __construct(?CategoryDao $dao = null)
    {
        $this->dao = $dao ?? new CategoryDao();
    }

    public function getAll(): array
    {
        return $this->dao->get_all();
    }

    public function getById($id): array
    {
        $categoryId = $this->validateId($id);
        $category = $this->dao->get_by_id($categoryId);

        if ($category === null) {
            throw new RuntimeException('Category not found.', 404);
        }

        return $category;
    }

    public function create(array $data): array
    {
        $name = isset($data['name']) ? trim((string) $data['name']) : '';
        if ($name === '' || mb_strlen($name) > 100) {
            throw new InvalidArgumentException('Field "name" is required and must be at most 100 characters.', 400);
        }

        if ($this->dao->get_by_name($name) !== null) {
            throw new InvalidArgumentException('A category with this name already exists.', 400);
        }

        $description = null;
        if (array_key_exists('description', $data) && $data['description'] !== null && trim((string) $data['description']) !== '') {
            $description = trim((string) $data['description']);
        }

        $defaultPriority = isset($data['default_priority']) && $data['default_priority'] !== ''
            ? trim((string) $data['default_priority'])
            : 'medium';

        if (!in_array($defaultPriority, self::ALLOWED_PRIORITIES, true)) {
            throw new InvalidArgumentException(
                'Field "default_priority" must be one of: low, medium, high, urgent.',
                400
            );
        }

        return $this->dao->add([
            'name'             => $name,
            'description'      => $description,
            'default_priority' => $defaultPriority,
        ]);
    }

    public function update($id, array $data): array
    {
        $categoryId = $this->validateId($id);
        if ($this->dao->get_by_id($categoryId) === null) {
            throw new RuntimeException('Category not found.', 404);
        }

        $payload = [];

        if (array_key_exists('name', $data)) {
            $name = trim((string) $data['name']);
            if ($name === '' || mb_strlen($name) > 100) {
                throw new InvalidArgumentException('Field "name" cannot be empty and must be at most 100 characters.', 400);
            }
            $existingByName = $this->dao->get_by_name($name);
            if ($existingByName !== null && (int) $existingByName['id'] !== $categoryId) {
                throw new InvalidArgumentException('A category with this name already exists.', 400);
            }
            $payload['name'] = $name;
        }

        if (array_key_exists('description', $data)) {
            $payload['description'] = ($data['description'] === null || trim((string) $data['description']) === '')
                ? null
                : trim((string) $data['description']);
        }

        if (array_key_exists('default_priority', $data)) {
            $defaultPriority = trim((string) $data['default_priority']);
            if (!in_array($defaultPriority, self::ALLOWED_PRIORITIES, true)) {
                throw new InvalidArgumentException(
                    'Field "default_priority" must be one of: low, medium, high, urgent.',
                    400
                );
            }
            $payload['default_priority'] = $defaultPriority;
        }

        if (empty($payload)) {
            throw new InvalidArgumentException('No valid fields provided for update.', 400);
        }

        $updated = $this->dao->update($categoryId, $payload);
        if ($updated === null) {
            throw new RuntimeException('Category not found.', 404);
        }

        return $updated;
    }

    public function delete($id): bool
    {
        $categoryId = $this->validateId($id);

        if (!$this->dao->delete($categoryId)) {
            throw new RuntimeException('Category not found.', 404);
        }

        return true;
    }

    private function validateId($id): int
    {
        if (filter_var($id, FILTER_VALIDATE_INT) === false || (int) $id <= 0) {
            throw new InvalidArgumentException('A valid positive numeric category ID is required.', 400);
        }

        return (int) $id;
    }
}

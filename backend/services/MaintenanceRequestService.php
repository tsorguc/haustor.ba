<?php
/**
 * Haustor.ba - Maintenance Request Service
 *
 * Encapsulates validation and lifecycle logic for maintenance requests.
 */

require_once __DIR__ . '/../dao/MaintenanceRequestDao.php';

class MaintenanceRequestService
{
    private const ALLOWED_PRIORITIES = ['low', 'medium', 'high', 'urgent'];
    private const ALLOWED_STATUSES = ['pending', 'in_progress', 'resolved', 'rejected'];

    private MaintenanceRequestDao $dao;

    public function __construct(?MaintenanceRequestDao $dao = null)
    {
        $this->dao = $dao ?? new MaintenanceRequestDao();
    }

    public function getAll(array $filters = []): array
    {
        if (isset($filters['building_id']) && $filters['building_id'] !== '') {
            return $this->dao->get_by_building($this->validatePositiveInt($filters['building_id'], 'building_id'));
        }

        if (isset($filters['reported_by_user_id']) && $filters['reported_by_user_id'] !== '') {
            return $this->dao->get_by_user($this->validatePositiveInt($filters['reported_by_user_id'], 'reported_by_user_id'));
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $status = trim((string) $filters['status']);
            if (!in_array($status, self::ALLOWED_STATUSES, true)) {
                throw new InvalidArgumentException(
                    'Invalid status filter. Allowed values: pending, in_progress, resolved, rejected.',
                    400
                );
            }
            return $this->dao->get_by_status($status);
        }

        return $this->dao->get_all();
    }

    public function getById($id): array
    {
        $requestId = $this->validatePositiveInt($id, 'id');
        $request = $this->dao->get_by_id($requestId);

        if ($request === null) {
            throw new RuntimeException('Maintenance request not found.', 404);
        }

        return $request;
    }

    public function create(array $data): array
    {
        foreach (['building_id', 'reported_by_user_id', 'category_id'] as $requiredFk) {
            if (!array_key_exists($requiredFk, $data)) {
                throw new InvalidArgumentException(sprintf('Field "%s" is required.', $requiredFk), 400);
            }
        }

        $buildingId = $this->validatePositiveInt($data['building_id'], 'building_id');
        $reportedByUserId = $this->validatePositiveInt($data['reported_by_user_id'], 'reported_by_user_id');
        $categoryId = $this->validatePositiveInt($data['category_id'], 'category_id');

        $apartmentId = null;
        if (array_key_exists('apartment_id', $data) && $data['apartment_id'] !== null && $data['apartment_id'] !== '') {
            $apartmentId = $this->validatePositiveInt($data['apartment_id'], 'apartment_id');
        }

        $title = isset($data['title']) ? trim((string) $data['title']) : '';
        if ($title === '' || mb_strlen($title) > 150) {
            throw new InvalidArgumentException('Field "title" is required and must be at most 150 characters.', 400);
        }

        $description = isset($data['description']) ? trim((string) $data['description']) : '';
        if ($description === '') {
            throw new InvalidArgumentException('Field "description" is required.', 400);
        }

        $priority = isset($data['priority']) && $data['priority'] !== ''
            ? trim((string) $data['priority'])
            : 'medium';
        if (!in_array($priority, self::ALLOWED_PRIORITIES, true)) {
            throw new InvalidArgumentException('Field "priority" must be one of: low, medium, high, urgent.', 400);
        }

        $status = isset($data['status']) && $data['status'] !== ''
            ? trim((string) $data['status'])
            : 'pending';
        if (!in_array($status, self::ALLOWED_STATUSES, true)) {
            throw new InvalidArgumentException('Field "status" must be one of: pending, in_progress, resolved, rejected.', 400);
        }

        $resolutionNotes = null;
        if (array_key_exists('resolution_notes', $data) && $data['resolution_notes'] !== null && trim((string) $data['resolution_notes']) !== '') {
            $resolutionNotes = trim((string) $data['resolution_notes']);
        }

        $resolvedAt = null;
        if (array_key_exists('resolved_at', $data) && $data['resolved_at'] !== null && trim((string) $data['resolved_at']) !== '') {
            $resolvedAt = $this->validateDatetime((string) $data['resolved_at']);
        } elseif ($status === 'resolved') {
            $resolvedAt = date('Y-m-d H:i:s');
        }

        return $this->dao->add([
            'building_id'         => $buildingId,
            'apartment_id'        => $apartmentId,
            'reported_by_user_id' => $reportedByUserId,
            'category_id'         => $categoryId,
            'title'               => $title,
            'description'         => $description,
            'priority'            => $priority,
            'status'              => $status,
            'resolution_notes'    => $resolutionNotes,
            'resolved_at'         => $resolvedAt,
        ]);
    }

    public function update($id, array $data): array
    {
        $requestId = $this->validatePositiveInt($id, 'id');
        $existing = $this->dao->get_by_id($requestId);

        if ($existing === null) {
            throw new RuntimeException('Maintenance request not found.', 404);
        }

        $payload = [];

        if (array_key_exists('building_id', $data)) {
            $payload['building_id'] = $this->validatePositiveInt($data['building_id'], 'building_id');
        }
        if (array_key_exists('apartment_id', $data)) {
            $payload['apartment_id'] = ($data['apartment_id'] === null || $data['apartment_id'] === '')
                ? null
                : $this->validatePositiveInt($data['apartment_id'], 'apartment_id');
        }
        if (array_key_exists('reported_by_user_id', $data)) {
            $payload['reported_by_user_id'] = $this->validatePositiveInt($data['reported_by_user_id'], 'reported_by_user_id');
        }
        if (array_key_exists('category_id', $data)) {
            $payload['category_id'] = $this->validatePositiveInt($data['category_id'], 'category_id');
        }
        if (array_key_exists('title', $data)) {
            $title = trim((string) $data['title']);
            if ($title === '' || mb_strlen($title) > 150) {
                throw new InvalidArgumentException('Field "title" cannot be empty and must be at most 150 characters.', 400);
            }
            $payload['title'] = $title;
        }
        if (array_key_exists('description', $data)) {
            $description = trim((string) $data['description']);
            if ($description === '') {
                throw new InvalidArgumentException('Field "description" cannot be empty.', 400);
            }
            $payload['description'] = $description;
        }
        if (array_key_exists('priority', $data)) {
            $priority = trim((string) $data['priority']);
            if (!in_array($priority, self::ALLOWED_PRIORITIES, true)) {
                throw new InvalidArgumentException('Field "priority" must be one of: low, medium, high, urgent.', 400);
            }
            $payload['priority'] = $priority;
        }
        if (array_key_exists('status', $data)) {
            $status = trim((string) $data['status']);
            if (!in_array($status, self::ALLOWED_STATUSES, true)) {
                throw new InvalidArgumentException('Field "status" must be one of: pending, in_progress, resolved, rejected.', 400);
            }
            $payload['status'] = $status;
        }
        if (array_key_exists('resolution_notes', $data)) {
            $payload['resolution_notes'] = ($data['resolution_notes'] === null || trim((string) $data['resolution_notes']) === '')
                ? null
                : trim((string) $data['resolution_notes']);
        }
        if (array_key_exists('resolved_at', $data)) {
            $payload['resolved_at'] = ($data['resolved_at'] === null || trim((string) $data['resolved_at']) === '')
                ? null
                : $this->validateDatetime((string) $data['resolved_at']);
        } elseif (($payload['status'] ?? null) === 'resolved' && empty($existing['resolved_at'])) {
            $payload['resolved_at'] = date('Y-m-d H:i:s');
        }

        if (empty($payload)) {
            throw new InvalidArgumentException('No valid fields provided for update.', 400);
        }

        $updated = $this->dao->update($requestId, $payload);
        if ($updated === null) {
            throw new RuntimeException('Maintenance request not found.', 404);
        }

        return $updated;
    }

    public function delete($id): bool
    {
        $requestId = $this->validatePositiveInt($id, 'id');

        if (!$this->dao->delete($requestId)) {
            throw new RuntimeException('Maintenance request not found.', 404);
        }

        return true;
    }

    private function validatePositiveInt($value, string $field): int
    {
        if (filter_var($value, FILTER_VALIDATE_INT) === false || (int) $value <= 0) {
            throw new InvalidArgumentException(sprintf('Field "%s" must be a positive integer.', $field), 400);
        }

        return (int) $value;
    }

    private function validateDatetime(string $value): string
    {
        $trimmed = trim($value);
        $timestamp = strtotime($trimmed);
        if ($timestamp === false) {
            throw new InvalidArgumentException('Field "resolved_at" must be a valid datetime string.', 400);
        }

        return date('Y-m-d H:i:s', $timestamp);
    }
}

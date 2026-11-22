<?php
/**
 * Haustor.ba - Building Service
 *
 * Encapsulates validation and business logic for residential buildings.
 */

require_once __DIR__ . '/../dao/BuildingDao.php';

class BuildingService
{
    private BuildingDao $dao;

    public function __construct(?BuildingDao $dao = null)
    {
        $this->dao = $dao ?? new BuildingDao();
    }

    public function getAll($managerId = null): array
    {
        if ($managerId !== null && $managerId !== '') {
            return $this->dao->get_by_manager($this->validatePositiveInt($managerId, 'manager_id'));
        }

        return $this->dao->get_all();
    }

    public function getById($id): array
    {
        $buildingId = $this->validatePositiveInt($id, 'id');
        $building = $this->dao->get_by_id($buildingId);

        if ($building === null) {
            throw new RuntimeException('Building not found.', 404);
        }

        return $building;
    }

    public function create(array $data): array
    {
        $name = $this->requireString($data, 'name', 150);
        $address = $this->requireString($data, 'address', 255);
        $city = $this->requireString($data, 'city', 100);
        $postalCode = $this->requireString($data, 'postal_code', 20);

        $latitude = $this->parseCoordinate($data, 'latitude', -90.0, 90.0);
        $longitude = $this->parseCoordinate($data, 'longitude', -180.0, 180.0);

        $totalFloors = 1;
        if (array_key_exists('total_floors', $data) && $data['total_floors'] !== null && $data['total_floors'] !== '') {
            $totalFloors = $this->validatePositiveInt($data['total_floors'], 'total_floors');
        }

        $managerId = null;
        if (array_key_exists('manager_id', $data) && $data['manager_id'] !== null && $data['manager_id'] !== '') {
            $managerId = $this->validatePositiveInt($data['manager_id'], 'manager_id');
        }

        return $this->dao->add([
            'name'         => $name,
            'address'      => $address,
            'city'         => $city,
            'postal_code'  => $postalCode,
            'latitude'     => $latitude,
            'longitude'    => $longitude,
            'total_floors' => $totalFloors,
            'manager_id'   => $managerId,
        ]);
    }

    public function update($id, array $data): array
    {
        $buildingId = $this->validatePositiveInt($id, 'id');
        if ($this->dao->get_by_id($buildingId) === null) {
            throw new RuntimeException('Building not found.', 404);
        }

        $payload = [];

        if (array_key_exists('name', $data)) {
            $payload['name'] = $this->requireString($data, 'name', 150);
        }
        if (array_key_exists('address', $data)) {
            $payload['address'] = $this->requireString($data, 'address', 255);
        }
        if (array_key_exists('city', $data)) {
            $payload['city'] = $this->requireString($data, 'city', 100);
        }
        if (array_key_exists('postal_code', $data)) {
            $payload['postal_code'] = $this->requireString($data, 'postal_code', 20);
        }
        if (array_key_exists('latitude', $data)) {
            $payload['latitude'] = $this->parseCoordinate($data, 'latitude', -90.0, 90.0);
        }
        if (array_key_exists('longitude', $data)) {
            $payload['longitude'] = $this->parseCoordinate($data, 'longitude', -180.0, 180.0);
        }
        if (array_key_exists('total_floors', $data)) {
            $payload['total_floors'] = $this->validatePositiveInt($data['total_floors'], 'total_floors');
        }
        if (array_key_exists('manager_id', $data)) {
            $payload['manager_id'] = ($data['manager_id'] === null || $data['manager_id'] === '')
                ? null
                : $this->validatePositiveInt($data['manager_id'], 'manager_id');
        }

        if (empty($payload)) {
            throw new InvalidArgumentException('No valid fields provided for update.', 400);
        }

        $updated = $this->dao->update($buildingId, $payload);
        if ($updated === null) {
            throw new RuntimeException('Building not found.', 404);
        }

        return $updated;
    }

    public function delete($id): bool
    {
        $buildingId = $this->validatePositiveInt($id, 'id');

        if (!$this->dao->delete($buildingId)) {
            throw new RuntimeException('Building not found.', 404);
        }

        return true;
    }

    private function requireString(array $data, string $field, int $maxLength): string
    {
        $value = isset($data[$field]) ? trim((string) $data[$field]) : '';
        if ($value === '' || mb_strlen($value) > $maxLength) {
            throw new InvalidArgumentException(
                sprintf('Field "%s" is required and must be at most %d characters.', $field, $maxLength),
                400
            );
        }

        return $value;
    }

    private function parseCoordinate(array $data, string $field, float $min, float $max): ?float
    {
        if (!array_key_exists($field, $data) || $data[$field] === null || $data[$field] === '') {
            return null;
        }

        if (!is_numeric($data[$field])) {
            throw new InvalidArgumentException(sprintf('Field "%s" must be a valid number.', $field), 400);
        }

        $val = (float) $data[$field];
        if ($val < $min || $val > $max) {
            throw new InvalidArgumentException(
                sprintf('Field "%s" must be between %.1f and %.1f.', $field, $min, $max),
                400
            );
        }

        return $val;
    }

    private function validatePositiveInt($value, string $field): int
    {
        if (filter_var($value, FILTER_VALIDATE_INT) === false || (int) $value <= 0) {
            throw new InvalidArgumentException(sprintf('Field "%s" must be a positive integer.', $field), 400);
        }

        return (int) $value;
    }
}

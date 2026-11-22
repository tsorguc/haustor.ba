<?php
/**
 * Haustor.ba - Apartment Service
 *
 * Encapsulates validation and business logic for apartment units.
 */

require_once __DIR__ . '/../dao/ApartmentDao.php';

class ApartmentService
{
    private ApartmentDao $dao;

    public function __construct(?ApartmentDao $dao = null)
    {
        $this->dao = $dao ?? new ApartmentDao();
    }

    public function getAll($buildingId = null, $residentId = null): array
    {
        if ($buildingId !== null && $buildingId !== '') {
            return $this->dao->get_by_building($this->validatePositiveInt($buildingId, 'building_id'));
        }

        if ($residentId !== null && $residentId !== '') {
            return $this->dao->get_by_resident($this->validatePositiveInt($residentId, 'resident_id'));
        }

        return $this->dao->get_all();
    }

    public function getById($id): array
    {
        $apartmentId = $this->validatePositiveInt($id, 'id');
        $apartment = $this->dao->get_by_id($apartmentId);

        if ($apartment === null) {
            throw new RuntimeException('Apartment not found.', 404);
        }

        return $apartment;
    }

    public function create(array $data): array
    {
        if (!array_key_exists('building_id', $data)) {
            throw new InvalidArgumentException('Field "building_id" is required.', 400);
        }
        $buildingId = $this->validatePositiveInt($data['building_id'], 'building_id');

        $apartmentNumber = isset($data['apartment_number']) ? trim((string) $data['apartment_number']) : '';
        if ($apartmentNumber === '' || mb_strlen($apartmentNumber) > 20) {
            throw new InvalidArgumentException('Field "apartment_number" is required and must be at most 20 characters.', 400);
        }

        $this->assertUniqueUnitInBuilding($buildingId, $apartmentNumber);

        $residentId = null;
        if (array_key_exists('resident_id', $data) && $data['resident_id'] !== null && $data['resident_id'] !== '') {
            $residentId = $this->validatePositiveInt($data['resident_id'], 'resident_id');
        }

        $floor = 0;
        if (array_key_exists('floor', $data) && $data['floor'] !== null && $data['floor'] !== '') {
            $floor = $this->validateInt($data['floor'], 'floor');
        }

        $areaSqm = $this->parseArea($data);

        return $this->dao->add([
            'building_id'      => $buildingId,
            'resident_id'      => $residentId,
            'apartment_number' => $apartmentNumber,
            'floor'            => $floor,
            'area_sqm'         => $areaSqm,
        ]);
    }

    public function update($id, array $data): array
    {
        $apartmentId = $this->validatePositiveInt($id, 'id');
        $existing = $this->dao->get_by_id($apartmentId);

        if ($existing === null) {
            throw new RuntimeException('Apartment not found.', 404);
        }

        $payload = [];

        if (array_key_exists('building_id', $data)) {
            $payload['building_id'] = $this->validatePositiveInt($data['building_id'], 'building_id');
        }

        if (array_key_exists('resident_id', $data)) {
            $payload['resident_id'] = ($data['resident_id'] === null || $data['resident_id'] === '')
                ? null
                : $this->validatePositiveInt($data['resident_id'], 'resident_id');
        }

        if (array_key_exists('apartment_number', $data)) {
            $apartmentNumber = trim((string) $data['apartment_number']);
            if ($apartmentNumber === '' || mb_strlen($apartmentNumber) > 20) {
                throw new InvalidArgumentException('Field "apartment_number" cannot be empty and must be at most 20 characters.', 400);
            }
            $payload['apartment_number'] = $apartmentNumber;
        }

        if (array_key_exists('floor', $data)) {
            $payload['floor'] = $this->validateInt($data['floor'], 'floor');
        }

        if (array_key_exists('area_sqm', $data)) {
            $payload['area_sqm'] = $this->parseArea($data);
        }

        if (empty($payload)) {
            throw new InvalidArgumentException('No valid fields provided for update.', 400);
        }

        if (array_key_exists('building_id', $payload) || array_key_exists('apartment_number', $payload)) {
            $targetBuildingId = $payload['building_id'] ?? (int) $existing['building_id'];
            $targetNumber = $payload['apartment_number'] ?? (string) $existing['apartment_number'];
            $this->assertUniqueUnitInBuilding($targetBuildingId, $targetNumber, $apartmentId);
        }

        $updated = $this->dao->update($apartmentId, $payload);
        if ($updated === null) {
            throw new RuntimeException('Apartment not found.', 404);
        }

        return $updated;
    }

    public function delete($id): bool
    {
        $apartmentId = $this->validatePositiveInt($id, 'id');

        if (!$this->dao->delete($apartmentId)) {
            throw new RuntimeException('Apartment not found.', 404);
        }

        return true;
    }

    private function assertUniqueUnitInBuilding(int $buildingId, string $apartmentNumber, ?int $ignoreId = null): void
    {
        $existingUnits = $this->dao->get_by_building($buildingId);
        foreach ($existingUnits as $unit) {
            if ($ignoreId !== null && (int) $unit['id'] === $ignoreId) {
                continue;
            }
            if (mb_strtolower((string) $unit['apartment_number']) === mb_strtolower($apartmentNumber)) {
                throw new InvalidArgumentException('An apartment with this number already exists in the selected building.', 400);
            }
        }
    }

    private function parseArea(array $data): ?float
    {
        if (!array_key_exists('area_sqm', $data) || $data['area_sqm'] === null || $data['area_sqm'] === '') {
            return null;
        }

        if (!is_numeric($data['area_sqm']) || (float) $data['area_sqm'] <= 0) {
            throw new InvalidArgumentException('Field "area_sqm" must be a positive number.', 400);
        }

        return round((float) $data['area_sqm'], 2);
    }

    private function validatePositiveInt($value, string $field): int
    {
        if (filter_var($value, FILTER_VALIDATE_INT) === false || (int) $value <= 0) {
            throw new InvalidArgumentException(sprintf('Field "%s" must be a positive integer.', $field), 400);
        }

        return (int) $value;
    }

    private function validateInt($value, string $field): int
    {
        if (filter_var($value, FILTER_VALIDATE_INT) === false) {
            throw new InvalidArgumentException(sprintf('Field "%s" must be an integer.', $field), 400);
        }

        return (int) $value;
    }
}

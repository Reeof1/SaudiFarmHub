<?php
declare(strict_types=1);

namespace Models;

use Core\BaseModel;
use PDO;

class Farm extends BaseModel
{
    public function getPaginated(int $limit, int $offset): array
    {
        $stmt = $this->db->prepare(
            'SELECT f.*, u.name AS owner_name 
             FROM farms f 
             JOIN users u ON f.owner_id = u.id 
             WHERE f.is_active = 1 AND f.approval_status = \'approved\'
             ORDER BY f.created_at DESC 
             LIMIT :limit OFFSET :offset'
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countActive(): int
    {
        $stmt = $this->db->query('SELECT COUNT(*) AS cnt FROM farms WHERE is_active = 1 AND approval_status = \'approved\'');
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($row['cnt'] ?? 0);
    }

    public function getByOwnerId(int $ownerId, int $limit, int $offset): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM farms 
             WHERE owner_id = :owner_id
             ORDER BY created_at DESC
             LIMIT :limit OFFSET :offset'
        );
        $stmt->bindValue(':owner_id', $ownerId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countByOwnerId(int $ownerId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) AS cnt FROM farms WHERE owner_id = :owner_id');
        $stmt->execute(['owner_id' => $ownerId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($row['cnt'] ?? 0);
    }

    public function countByApprovalStatus(string $status): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) AS cnt FROM farms WHERE approval_status = :status');
        $stmt->execute(['status' => $status]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($row['cnt'] ?? 0);
    }

    public function getByIdForOwner(int $id, int $ownerId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM farms WHERE id = :id AND owner_id = :owner_id LIMIT 1');
        $stmt->execute(['id' => $id, 'owner_id' => $ownerId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO farms (owner_id, name, location, description, latitude, longitude, is_active, created_at)
             VALUES (:owner_id, :name, :location, :description, :latitude, :longitude, 1, NOW())'
        );
        $stmt->execute([
            'owner_id' => $data['owner_id'],
            'name' => $data['name'],
            'location' => $data['location'],
            'description' => $data['description'],
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, int $ownerId, array $data): void
    {
        $stmt = $this->db->prepare(
            'UPDATE farms
             SET name = :name, location = :location, description = :description,
                 latitude = :latitude, longitude = :longitude, updated_at = NOW()
             WHERE id = :id AND owner_id = :owner_id'
        );
        $stmt->execute([
            'id' => $id,
            'owner_id' => $ownerId,
            'name' => $data['name'],
            'location' => $data['location'],
            'description' => $data['description'],
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
        ]);
    }

    public function delete(int $id, int $ownerId): void
    {
        // Soft delete is safer; keep schema compatible with "is_active".
        $stmt = $this->db->prepare('UPDATE farms SET is_active = 0, updated_at = NOW() WHERE id = :id AND owner_id = :owner_id');
        $stmt->execute(['id' => $id, 'owner_id' => $ownerId]);
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT f.*, u.name AS owner_name
             FROM farms f
             LEFT JOIN users u ON f.owner_id = u.id
             WHERE f.id = :id AND f.is_active = 1 AND f.approval_status = \'approved\'
             LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function searchFiltered(array $filters, int $limit, int $offset): array
    {
        $joins = '';
        $wheres = ['f.is_active = 1', 'f.approval_status = \'approved\''];
        $params = [];

        if (!empty($filters['name'])) {
            $wheres[] = 'f.name LIKE :name';
            $params['name'] = '%' . $filters['name'] . '%';
        }

        if (!empty($filters['location'])) {
            $wheres[] = 'f.location LIKE :location';
            $params['location'] = '%' . $filters['location'] . '%';
        }

        $hasActivityType = !empty($filters['activity_type']);
        $hasAvailabilityDate = !empty($filters['availability_date']);
        $hasMinPrice = array_key_exists('min_price', $filters) && $filters['min_price'] !== '';
        $hasMaxPrice = array_key_exists('max_price', $filters) && $filters['max_price'] !== '';

        $needActivityJoin = $hasActivityType || $hasAvailabilityDate || $hasMinPrice || $hasMaxPrice;
        if ($needActivityJoin) {
            $joins .= ' JOIN activities a ON a.farm_id = f.id AND a.is_active = 1 ';
        }

        if ($hasActivityType && $needActivityJoin) {
            $wheres[] = 'a.activity_type = :activity_type';
            $params['activity_type'] = $filters['activity_type'];
        }

        $needScheduleJoin = $hasAvailabilityDate || $hasMinPrice || $hasMaxPrice;
        if ($needScheduleJoin) {
            $joins .= ' JOIN schedules s ON s.activity_id = a.id AND s.is_active = 1 ';
        }

        if ($hasAvailabilityDate && $needScheduleJoin) {
            $wheres[] = 's.schedule_date = :availability_date';
            $params['availability_date'] = $filters['availability_date'];
            // Remaining capacity must be > 0.
            $wheres[] = '(s.capacity - (
                SELECT COALESCE(SUM(b.party_size), 0)
                FROM bookings b
                WHERE b.schedule_id = s.id
                  AND b.status_id IN (1, 2)
            )) > 0';
        }

        if ($hasMinPrice) {
            $wheres[] = 's.price >= :min_price';
            $params['min_price'] = (float)$filters['min_price'];
        }

        if ($hasMaxPrice) {
            $wheres[] = 's.price <= :max_price';
            $params['max_price'] = (float)$filters['max_price'];
        }

        $sql = 'SELECT DISTINCT f.*, u.name AS owner_name
                FROM farms f
                JOIN users u ON f.owner_id = u.id
                ' . $joins . '
                WHERE ' . implode(' AND ', $wheres) . '
                ORDER BY f.created_at DESC
                LIMIT :limit OFFSET :offset';

        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue(':' . $k, $v);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPaginatedSortedByDistance(float $lat, float $lng, int $limit, int $offset): array
    {
        // Haversine formula: distance in kilometers between two lat/lng points.
        // Farms without coordinates are placed last (NULL -> very large distance).
        $sql = 'SELECT f.*, u.name AS owner_name,
                (
                    CASE
                        WHEN f.latitude IS NULL OR f.longitude IS NULL THEN NULL
                        ELSE (
                            6371 * ACOS(
                                LEAST(1, GREATEST(-1,
                                    COS(RADIANS(:lat1)) * COS(RADIANS(f.latitude))
                                    * COS(RADIANS(f.longitude) - RADIANS(:lng1))
                                    + SIN(RADIANS(:lat2)) * SIN(RADIANS(f.latitude))
                                ))
                            )
                        )
                    END
                ) AS distance_km
                FROM farms f
                JOIN users u ON f.owner_id = u.id
                WHERE f.is_active = 1 AND f.approval_status = \'approved\'
                ORDER BY (distance_km IS NULL), distance_km ASC, f.created_at DESC
                LIMIT :limit OFFSET :offset';

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':lat1', $lat);
        $stmt->bindValue(':lng1', $lng);
        $stmt->bindValue(':lat2', $lat);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByApprovalStatus(string $status): array
    {
        $stmt = $this->db->prepare(
            'SELECT f.*, u.name AS owner_name
             FROM farms f
             JOIN users u ON f.owner_id = u.id
             WHERE f.approval_status = :status
             ORDER BY f.created_at DESC'
        );
        $stmt->execute(['status' => $status]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function setApprovalStatus(int $farmId, string $status): void
    {
        $stmt = $this->db->prepare(
            'UPDATE farms
             SET approval_status = :status, updated_at = NOW()
             WHERE id = :id'
        );
        $stmt->execute(['status' => $status, 'id' => $farmId]);
    }
}


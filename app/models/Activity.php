<?php
declare(strict_types=1);

namespace Models;

use Core\BaseModel;
use PDO;

class Activity extends BaseModel
{
    public function getActiveByFarmId(int $farmId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM activities
             WHERE farm_id = :farm_id AND is_active = 1
             ORDER BY name ASC'
        );
        $stmt->execute(['farm_id' => $farmId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getActiveByType(string $activityType, int $limit = 50): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM activities
             WHERE activity_type = :activity_type AND is_active = 1
             ORDER BY name ASC
             LIMIT :limit'
        );
        $stmt->bindValue(':activity_type', $activityType);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO activities (farm_id, name, activity_type, description, is_active, created_at)
             VALUES (:farm_id, :name, :activity_type, :description, 1, NOW())'
        );
        $stmt->execute([
            'farm_id' => $data['farm_id'],
            'name' => $data['name'],
            'activity_type' => $data['activity_type'],
            'description' => $data['description'],
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function getByIdForOwner(int $activityId, int $ownerId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT a.*, f.owner_id
             FROM activities a
             JOIN farms f ON a.farm_id = f.id
             WHERE a.id = :id AND f.owner_id = :owner_id AND a.is_active = 1 AND f.is_active = 1
             LIMIT 1'
        );
        $stmt->execute(['id' => $activityId, 'owner_id' => $ownerId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->db->prepare(
            'UPDATE activities
             SET name = :name,
                 activity_type = :activity_type,
                 description = :description
             WHERE id = :id'
        );
        $stmt->execute([
            'id' => $id,
            'name' => $data['name'],
            'activity_type' => $data['activity_type'],
            'description' => $data['description'],
        ]);
    }

    public function softDelete(int $id): void
    {
        $stmt = $this->db->prepare(
            'UPDATE activities
             SET is_active = 0
             WHERE id = :id'
        );
        $stmt->execute(['id' => $id]);
    }
}


<?php
declare(strict_types=1);

namespace Models;

use Core\BaseModel;
use PDO;

class Schedule extends BaseModel
{
    public function getById(int $scheduleId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT s.*, 
                    a.farm_id,
                    f.owner_id
             FROM schedules s
             JOIN activities a ON s.activity_id = a.id
             JOIN farms f ON a.farm_id = f.id
             WHERE s.id = :id AND s.is_active = 1 AND a.is_active = 1 AND f.is_active = 1
             LIMIT 1'
        );
        $stmt->execute(['id' => $scheduleId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function getByIdForOwner(int $scheduleId, int $ownerId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT s.*, a.farm_id, f.owner_id
             FROM schedules s
             JOIN activities a ON s.activity_id = a.id
             JOIN farms f ON a.farm_id = f.id
             WHERE s.id = :id
               AND s.is_active = 1
               AND a.is_active = 1
               AND f.is_active = 1
               AND f.owner_id = :owner_id
             LIMIT 1'
        );
        $stmt->execute(['id' => $scheduleId, 'owner_id' => $ownerId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function getByActivityForOwner(int $activityId, int $ownerId): array
    {
        $stmt = $this->db->prepare(
            'SELECT s.*, a.farm_id, f.owner_id
             FROM schedules s
             JOIN activities a ON s.activity_id = a.id
             JOIN farms f ON a.farm_id = f.id
             WHERE s.activity_id = :activity_id
               AND f.owner_id = :owner_id
               AND s.is_active = 1
               AND a.is_active = 1
               AND f.is_active = 1
             ORDER BY s.schedule_date ASC, s.start_time ASC'
        );
        $stmt->execute(['activity_id' => $activityId, 'owner_id' => $ownerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createForActivity(int $activityId, array $data): bool
    {
        $stmt = $this->db->prepare(
            'INSERT INTO schedules (activity_id, schedule_date, start_time, end_time, capacity, price, is_active, created_at)
             VALUES (:activity_id, :schedule_date, :start_time, :end_time, :capacity, :price, 1, NOW())'
        );
        try {
            return $stmt->execute([
                'activity_id' => $activityId,
                'schedule_date' => $data['schedule_date'],
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'capacity' => $data['capacity'],
                'price' => $data['price'],
            ]);
        } catch (\PDOException $e) {
            // Likely duplicate schedule (unique constraint); let caller handle failure.
            return false;
        }
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->db->prepare(
            'UPDATE schedules
             SET schedule_date = :schedule_date,
                 start_time = :start_time,
                 end_time = :end_time,
                 capacity = :capacity,
                 price = :price,
                 updated_at = NOW()
             WHERE id = :id'
        );
        $stmt->execute([
            'id' => $id,
            'schedule_date' => $data['schedule_date'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'capacity' => $data['capacity'],
            'price' => $data['price'],
        ]);
    }

    public function softDelete(int $id): void
    {
        $stmt = $this->db->prepare(
            'UPDATE schedules
             SET is_active = 0, updated_at = NOW()
             WHERE id = :id'
        );
        $stmt->execute(['id' => $id]);
    }

    public function getAvailableByActivityDate(int $activityId, string $date): array
    {
        // Basic availability: return active schedules for that date.
        // Client-side UI will further filter by remaining capacity.
        $stmt = $this->db->prepare(
            'SELECT s.*
             FROM schedules s
             WHERE s.activity_id = :activity_id
               AND s.schedule_date = :schedule_date
               AND s.is_active = 1
             ORDER BY s.start_time ASC'
        );
        $stmt->execute([
            'activity_id' => $activityId,
            'schedule_date' => $date,
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}


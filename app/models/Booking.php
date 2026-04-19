<?php
declare(strict_types=1);

namespace Models;

use Core\BaseModel;
use PDO;

class Booking extends BaseModel
{
    private const STATUS_PENDING = 1;
    private const STATUS_APPROVED = 2;
    private const STATUS_CANCELLED = 3;
    private const STATUS_COMPLETED = 4;

    public function createBooking(int $visitorId, int $scheduleId, int $partySize): array
    {
        if ($partySize < 1 || $partySize > 50) {
            throw new \InvalidArgumentException('Invalid party size.');
        }

        // Load schedule + owning farm to validate access and derive owner notifications.
        $scheduleStmt = $this->db->prepare(
            'SELECT s.*, 
                    a.farm_id,
                    f.owner_id
             FROM schedules s
             JOIN activities a ON s.activity_id = a.id
             JOIN farms f ON a.farm_id = f.id
             WHERE s.id = :scheduleId
               AND s.is_active = 1
               AND a.is_active = 1
               AND f.is_active = 1
             LIMIT 1'
        );
        $scheduleStmt->execute(['scheduleId' => $scheduleId]);
        $schedule = $scheduleStmt->fetch(PDO::FETCH_ASSOC);
        if (!$schedule) {
            throw new \RuntimeException('Schedule not available.');
        }

        // Prevent double booking for the same visitor + schedule (Pending/Approved only).
        $dupStmt = $this->db->prepare(
            'SELECT COUNT(*) AS cnt
             FROM bookings
             WHERE visitor_id = :visitor_id
               AND schedule_id = :schedule_id
               AND status_id IN (:pending, :approved)'
        );
        $dupStmt->bindValue(':visitor_id', $visitorId, PDO::PARAM_INT);
        $dupStmt->bindValue(':schedule_id', $scheduleId, PDO::PARAM_INT);
        $dupStmt->bindValue(':pending', self::STATUS_PENDING, PDO::PARAM_INT);
        $dupStmt->bindValue(':approved', self::STATUS_APPROVED, PDO::PARAM_INT);
        $dupStmt->execute();
        $dupRow = $dupStmt->fetch(PDO::FETCH_ASSOC);
        if ((int)($dupRow['cnt'] ?? 0) > 0) {
            throw new \RuntimeException('You already have a booking for this time.');
        }

        // Validate capacity (Pending/Approved only count toward remaining spots).
        $capStmt = $this->db->prepare(
            'SELECT COALESCE(SUM(party_size), 0) AS booked_total
             FROM bookings
             WHERE schedule_id = :schedule_id
               AND status_id IN (:pending, :approved)'
        );
        $capStmt->bindValue(':schedule_id', $scheduleId, PDO::PARAM_INT);
        $capStmt->bindValue(':pending', self::STATUS_PENDING, PDO::PARAM_INT);
        $capStmt->bindValue(':approved', self::STATUS_APPROVED, PDO::PARAM_INT);
        $capStmt->execute();
        $capRow = $capStmt->fetch(PDO::FETCH_ASSOC);
        $bookedTotal = (int)($capRow['booked_total'] ?? 0);

        $remaining = (int)$schedule['capacity'] - $bookedTotal;
        if ($remaining < $partySize) {
            throw new \RuntimeException('Not enough capacity for the selected time.');
        }

        $this->db->beginTransaction();
        try {
            $insertStmt = $this->db->prepare(
                'INSERT INTO bookings (visitor_id, schedule_id, party_size, status_id, created_at)
                 VALUES (:visitor_id, :schedule_id, :party_size, :status_id, NOW())'
            );
            $insertStmt->execute([
                'visitor_id' => $visitorId,
                'schedule_id' => $scheduleId,
                'party_size' => $partySize,
                'status_id' => self::STATUS_PENDING,
            ]);
            $bookingId = (int)$this->db->lastInsertId();

            // Notifications: visitor + farm owner.
            $visitorMsg = sprintf(
                'Booking submitted for %s %s (Party size: %d).',
                (string)$schedule['schedule_date'],
                (string)$schedule['start_time'],
                $partySize
            );
            $ownerMsg = sprintf(
                'New booking submitted (Pending) for %s %s. Booking ID: %d.',
                (string)$schedule['schedule_date'],
                (string)$schedule['start_time'],
                $bookingId
            );

            $notif = new Notification();
            $notif->create($visitorId, 'booking', $visitorMsg);
            $notif->create((int)$schedule['owner_id'], 'owner_alert', $ownerMsg);

            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }

        return [
            'id' => $bookingId,
            'status' => 'Pending',
        ];
    }

    public function cancelBooking(int $bookingId, int $actorId, string $actorRole): array
    {
        $bookingStmt = $this->db->prepare(
            'SELECT b.*, 
                    s.activity_id,
                    a.farm_id,
                    f.owner_id,
                    bs.name AS status_name
             FROM bookings b
             JOIN schedules s ON b.schedule_id = s.id
             JOIN activities a ON s.activity_id = a.id
             JOIN farms f ON a.farm_id = f.id
             JOIN booking_status bs ON b.status_id = bs.id
             WHERE b.id = :id
             LIMIT 1'
        );
        $bookingStmt->execute(['id' => $bookingId]);
        $booking = $bookingStmt->fetch(PDO::FETCH_ASSOC);
        if (!$booking) {
            throw new \RuntimeException('Booking not found.');
        }

        $currentStatus = (string)$booking['status_name'];
        if (in_array($currentStatus, ['Cancelled', 'Completed'], true)) {
            throw new \RuntimeException('This booking can no longer be cancelled.');
        }

        $allowed = false;
        if ($actorRole === 'visitor') {
            $allowed = ((int)$booking['visitor_id'] === $actorId) && in_array($currentStatus, ['Pending', 'Approved'], true);
        } elseif (in_array($actorRole, ['owner', 'admin'], true)) {
            if ($actorRole === 'admin') {
                $allowed = in_array($currentStatus, ['Pending', 'Approved'], true);
            } else {
                $allowed = ((int)$booking['owner_id'] === $actorId) && in_array($currentStatus, ['Pending', 'Approved'], true);
            }
        }

        if (!$allowed) {
            throw new \RuntimeException('Access denied to cancel this booking.');
        }

        $this->db->beginTransaction();
        try {
            $statusId = self::STATUS_CANCELLED;
            $upd = $this->db->prepare(
                'UPDATE bookings
                 SET status_id = :status_id, updated_at = NOW(), cancelled_at = NOW()
                 WHERE id = :id'
            );
            $upd->execute(['status_id' => $statusId, 'id' => $bookingId]);

            $notif = new Notification();
            $visitorMsg = sprintf('Your booking (ID: %d) was cancelled.', $bookingId);
            $ownerMsg = sprintf('A booking (ID: %d) was cancelled.', $bookingId);
            $notif->create((int)$booking['visitor_id'], 'booking', $visitorMsg);
            $notif->create((int)$booking['owner_id'], 'owner_alert', $ownerMsg);

            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }

        return ['id' => $bookingId, 'status' => 'Cancelled'];
    }

    public function updateStatus(int $bookingId, string $newStatus, int $actorId, string $actorRole): array
    {
        $bookingStmt = $this->db->prepare(
            'SELECT b.*, 
                    bs.name AS status_name,
                    f.owner_id
             FROM bookings b
             JOIN schedules s ON b.schedule_id = s.id
             JOIN activities a ON s.activity_id = a.id
             JOIN farms f ON a.farm_id = f.id
             JOIN booking_status bs ON b.status_id = bs.id
             WHERE b.id = :id
             LIMIT 1'
        );
        $bookingStmt->execute(['id' => $bookingId]);
        $booking = $bookingStmt->fetch(PDO::FETCH_ASSOC);
        if (!$booking) {
            throw new \RuntimeException('Booking not found.');
        }

        $currentStatus = (string)$booking['status_name'];

        $allowed = false;
        $targetStatusId = null;
        if ($newStatus === 'Approved') {
            $targetStatusId = self::STATUS_APPROVED;
            $allowed = ($currentStatus === 'Pending') && in_array($actorRole, ['owner', 'admin'], true) &&
                ($actorRole === 'admin' || (int)$booking['owner_id'] === $actorId);
        } elseif ($newStatus === 'Completed') {
            $targetStatusId = self::STATUS_COMPLETED;
            $allowed = ($currentStatus === 'Approved') && in_array($actorRole, ['owner', 'admin'], true) &&
                ($actorRole === 'admin' || (int)$booking['owner_id'] === $actorId);
        } else {
            throw new \RuntimeException('Invalid status transition request.');
        }

        if (!$allowed || $targetStatusId === null) {
            throw new \RuntimeException('Access denied or invalid transition.');
        }

        $this->db->beginTransaction();
        try {
            $upd = $this->db->prepare(
                'UPDATE bookings
                 SET status_id = :status_id, updated_at = NOW()
                 WHERE id = :id'
            );
            $upd->execute(['status_id' => $targetStatusId, 'id' => $bookingId]);

            $notif = new Notification();
            $visitorMsg = sprintf('Your booking (ID: %d) was updated to %s.', $bookingId, $newStatus);
            $notif->create((int)$booking['visitor_id'], 'booking', $visitorMsg);

            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }

        return ['id' => $bookingId, 'status' => $newStatus];
    }

    public function getBookedTotalForSchedule(int $scheduleId): int
    {
        $stmt = $this->db->prepare(
            'SELECT COALESCE(SUM(party_size), 0) AS booked_total
             FROM bookings
             WHERE schedule_id = :schedule_id
               AND status_id IN (:pending, :approved)'
        );
        $stmt->bindValue(':schedule_id', $scheduleId, PDO::PARAM_INT);
        $stmt->bindValue(':pending', self::STATUS_PENDING, PDO::PARAM_INT);
        $stmt->bindValue(':approved', self::STATUS_APPROVED, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($row['booked_total'] ?? 0);
    }

    public function getForVisitor(int $visitorId): array
    {
        $stmt = $this->db->prepare(
            'SELECT b.*,
                    bs.name AS status_name,
                    s.schedule_date,
                    s.start_time,
                    s.end_time,
                    s.price,
                    a.name AS activity_name,
                    f.name AS farm_name
             FROM bookings b
             JOIN booking_status bs ON b.status_id = bs.id
             JOIN schedules s ON b.schedule_id = s.id
             JOIN activities a ON s.activity_id = a.id
             JOIN farms f ON a.farm_id = f.id
             WHERE b.visitor_id = :visitor_id
             ORDER BY s.schedule_date DESC, s.start_time DESC, b.created_at DESC'
        );
        $stmt->execute(['visitor_id' => $visitorId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getForOwner(int $ownerId): array
    {
        $stmt = $this->db->prepare(
            'SELECT b.*,
                    bs.name AS status_name,
                    s.schedule_date,
                    s.start_time,
                    s.end_time,
                    s.price,
                    a.name AS activity_name,
                    f.name AS farm_name
             FROM bookings b
             JOIN booking_status bs ON b.status_id = bs.id
             JOIN schedules s ON b.schedule_id = s.id
             JOIN activities a ON s.activity_id = a.id
             JOIN farms f ON a.farm_id = f.id
             WHERE f.owner_id = :owner_id
             ORDER BY s.schedule_date DESC, s.start_time DESC, b.created_at DESC'
        );
        $stmt->execute(['owner_id' => $ownerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllForAdmin(): array
    {
        $stmt = $this->db->query(
            'SELECT b.*,
                    bs.name AS status_name,
                    s.schedule_date,
                    s.start_time,
                    s.end_time,
                    s.price,
                    a.name AS activity_name,
                    f.name AS farm_name,
                    u.name AS visitor_name
             FROM bookings b
             JOIN booking_status bs ON b.status_id = bs.id
             JOIN schedules s ON b.schedule_id = s.id
             JOIN activities a ON s.activity_id = a.id
             JOIN farms f ON a.farm_id = f.id
             JOIN users u ON b.visitor_id = u.id
             ORDER BY s.schedule_date DESC, s.start_time DESC, b.created_at DESC'
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getStatusCountsForVisitor(int $visitorId): array
    {
        $stmt = $this->db->prepare(
            'SELECT bs.name AS status_name, COUNT(*) AS cnt
             FROM bookings b
             JOIN booking_status bs ON b.status_id = bs.id
             WHERE b.visitor_id = :visitor_id
             GROUP BY bs.name'
        );
        $stmt->execute(['visitor_id' => $visitorId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $out = ['Pending' => 0, 'Approved' => 0, 'Cancelled' => 0, 'Completed' => 0];
        foreach ($rows as $r) {
            $name = (string)$r['status_name'];
            $out[$name] = (int)$r['cnt'];
        }
        return $out;
    }

    public function getStatusCountsForOwner(int $ownerId): array
    {
        $stmt = $this->db->prepare(
            'SELECT bs.name AS status_name, COUNT(*) AS cnt
             FROM bookings b
             JOIN booking_status bs ON b.status_id = bs.id
             JOIN schedules s ON b.schedule_id = s.id
             JOIN activities a ON s.activity_id = a.id
             JOIN farms f ON a.farm_id = f.id
             WHERE f.owner_id = :owner_id
             GROUP BY bs.name'
        );
        $stmt->execute(['owner_id' => $ownerId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $out = ['Pending' => 0, 'Approved' => 0, 'Cancelled' => 0, 'Completed' => 0];
        foreach ($rows as $r) {
            $name = (string)$r['status_name'];
            $out[$name] = (int)$r['cnt'];
        }
        return $out;
    }

    public function getStatusCountsForAdmin(): array
    {
        $stmt = $this->db->query(
            'SELECT bs.name AS status_name, COUNT(*) AS cnt
             FROM bookings b
             JOIN booking_status bs ON b.status_id = bs.id
             GROUP BY bs.name'
        );
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $out = ['Pending' => 0, 'Approved' => 0, 'Cancelled' => 0, 'Completed' => 0];
        foreach ($rows as $r) {
            $name = (string)$r['status_name'];
            $out[$name] = (int)$r['cnt'];
        }
        return $out;
    }
}


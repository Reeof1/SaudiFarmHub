<?php
declare(strict_types=1);

namespace Models;

use Core\BaseModel;
use PDO;

class Report extends BaseModel
{
    public function create(int $adminId, string $reportType, array $payload): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO reports (admin_id, report_type, payload, created_at)
             VALUES (:admin_id, :report_type, :payload, NOW())'
        );
        $stmt->execute([
            'admin_id' => $adminId,
            'report_type' => $reportType,
            'payload' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function getAll(): array
    {
        $stmt = $this->db->query(
            'SELECT r.*, u.name AS admin_name
             FROM reports r
             JOIN users u ON r.admin_id = u.id
             ORDER BY r.created_at DESC'
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}


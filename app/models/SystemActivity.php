<?php
declare(strict_types=1);

namespace Models;

use Core\BaseModel;
use PDO;

class SystemActivity extends BaseModel
{
    public function recent(int $limit = 30): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM (
                SELECT b.created_at AS event_time,
                       \'booking\' AS event_type,
                       CONCAT(\'Booking #\', b.id, \' created\') AS event_label,
                       CONCAT(\'Visitor ID: \', b.visitor_id) AS actor
                FROM bookings b

                UNION ALL

                SELECT f.created_at AS event_time,
                       \'farm\' AS event_type,
                       CONCAT(\'Farm published: \', f.name) AS event_label,
                       CONCAT(\'Owner ID: \', f.owner_id) AS actor
                FROM farms f

                UNION ALL

                SELECT u.created_at AS event_time,
                       \'user\' AS event_type,
                       CONCAT(\'User registered: \', u.email) AS event_label,
                       u.name AS actor
                FROM users u

                UNION ALL

                SELECT n.created_at AS event_time,
                       \'alert\' AS event_type,
                       LEFT(n.message, 120) AS event_label,
                       CONCAT(\'User ID: \', n.user_id) AS actor
                FROM notifications n

                UNION ALL

                SELECT r.created_at AS event_time,
                       \'report\' AS event_type,
                       CONCAT(\'Report generated: \', r.report_type) AS event_label,
                       CONCAT(\'Admin ID: \', r.admin_id) AS actor
                FROM reports r
            ) x
            ORDER BY x.event_time DESC
            LIMIT :limit'
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}


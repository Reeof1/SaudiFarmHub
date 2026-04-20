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
            'INSERT INTO farms (owner_id, name, city, description, main_image, latitude, longitude, is_active, created_at)
             VALUES (:owner_id, :name, :city, :description, :main_image, :latitude, :longitude, 1, NOW())'
        );
        $stmt->execute([
            'owner_id' => $data['owner_id'],
            'name' => $data['name'],
            'city' => $data['city'],
            'description' => $data['description'],
            'main_image' => $data['main_image'] ?? null,
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, int $ownerId, array $data): void
    {
        $fields = [
            'name = :name',
            'city = :city',
            'description = :description',
            'latitude = :latitude',
            'longitude = :longitude',
            'updated_at = NOW()',
        ];
        $params = [
            'id' => $id,
            'owner_id' => $ownerId,
            'name' => $data['name'],
            'city' => $data['city'],
            'description' => $data['description'],
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
        ];

        if (array_key_exists('main_image', $data)) {
            $fields[] = 'main_image = :main_image';
            $params['main_image'] = $data['main_image'];
        }

        $sql = 'UPDATE farms SET ' . implode(', ', $fields) . ' WHERE id = :id AND owner_id = :owner_id';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
    }

    public function getGalleryImages(int $farmId): array
    {
        $stmt = $this->db->prepare(
            'SELECT id, image_path, created_at
             FROM farm_images
             WHERE farm_id = :farm_id
             ORDER BY created_at ASC'
        );
        $stmt->execute(['farm_id' => $farmId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countGalleryImages(int $farmId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) AS cnt FROM farm_images WHERE farm_id = :farm_id');
        $stmt->execute(['farm_id' => $farmId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($row['cnt'] ?? 0);
    }

    public function addGalleryImage(int $farmId, string $imagePath): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO farm_images (farm_id, image_path, created_at)
             VALUES (:farm_id, :image_path, NOW())'
        );
        $stmt->execute([
            'farm_id' => $farmId,
            'image_path' => $imagePath,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function getGalleryImageForOwner(int $imageId, int $ownerId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT fi.id, fi.farm_id, fi.image_path
             FROM farm_images fi
             JOIN farms f ON fi.farm_id = f.id
             WHERE fi.id = :id AND f.owner_id = :owner_id
             LIMIT 1'
        );
        $stmt->execute(['id' => $imageId, 'owner_id' => $ownerId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function deleteGalleryImage(int $imageId): void
    {
        $stmt = $this->db->prepare('DELETE FROM farm_images WHERE id = :id');
        $stmt->execute(['id' => $imageId]);
    }

    public function recordVisit(int $farmId, int $userId): void
    {
        $stmt = $this->db->prepare(
            'INSERT IGNORE INTO farm_visits (farm_id, user_id, created_at)
             VALUES (:farm_id, :user_id, NOW())'
        );
        $stmt->execute(['farm_id' => $farmId, 'user_id' => $userId]);
    }

    public function countVisits(int $farmId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) AS cnt FROM farm_visits WHERE farm_id = :farm_id');
        $stmt->execute(['farm_id' => $farmId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($row['cnt'] ?? 0);
    }

    public function getVisitCountsByFarmIds(array $farmIds): array
    {
        if (empty($farmIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($farmIds), '?'));
        $sql = 'SELECT farm_id, COUNT(*) AS cnt
                FROM farm_visits
                WHERE farm_id IN (' . $placeholders . ')
                GROUP BY farm_id';

        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_map('intval', $farmIds));

        $result = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $result[(int)$row['farm_id']] = (int)$row['cnt'];
        }
        return $result;
    }

    public function addFavorite(int $userId, int $farmId): void
    {
        $stmt = $this->db->prepare(
            'INSERT IGNORE INTO favorites (user_id, farm_id, created_at)
             VALUES (:user_id, :farm_id, NOW())'
        );
        $stmt->execute(['user_id' => $userId, 'farm_id' => $farmId]);
    }

    public function removeFavorite(int $userId, int $farmId): void
    {
        $stmt = $this->db->prepare(
            'DELETE FROM favorites WHERE user_id = :user_id AND farm_id = :farm_id'
        );
        $stmt->execute(['user_id' => $userId, 'farm_id' => $farmId]);
    }

    public function isFavorite(int $userId, int $farmId): bool
    {
        $stmt = $this->db->prepare(
            'SELECT 1 FROM favorites WHERE user_id = :user_id AND farm_id = :farm_id LIMIT 1'
        );
        $stmt->execute(['user_id' => $userId, 'farm_id' => $farmId]);
        return (bool)$stmt->fetchColumn();
    }

    public function getFavoritesByUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT f.*, u.name AS owner_name, fav.created_at AS favorited_at
             FROM favorites fav
             JOIN farms f ON fav.farm_id = f.id
             JOIN users u ON f.owner_id = u.id
             WHERE fav.user_id = :user_id
               AND f.is_active = 1
               AND f.approval_status = \'approved\'
             ORDER BY fav.created_at DESC'
        );
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countFavoritesByUser(int $userId): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) AS cnt
             FROM favorites fav
             JOIN farms f ON fav.farm_id = f.id
             WHERE fav.user_id = :user_id
               AND f.is_active = 1
               AND f.approval_status = \'approved\''
        );
        $stmt->execute(['user_id' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($row['cnt'] ?? 0);
    }

    public function getFavoriteFarmIdsForUser(int $userId, array $farmIds): array
    {
        if (empty($farmIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($farmIds), '?'));
        $sql = 'SELECT farm_id FROM favorites
                WHERE user_id = ? AND farm_id IN (' . $placeholders . ')';

        $stmt = $this->db->prepare($sql);
        $params = array_merge([$userId], array_map('intval', $farmIds));
        $stmt->execute($params);

        $result = [];
        foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $id) {
            $result[(int)$id] = true;
        }
        return $result;
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

    public function searchFiltered(array $filters, int $limit, int $offset, ?float $userLat = null, ?float $userLng = null): array
    {
        $joins = '';
        $wheres = ['f.is_active = 1', 'f.approval_status = \'approved\''];
        $params = [];
        $sortByDistance = ($userLat !== null && $userLng !== null);

        if (!empty($filters['name'])) {
            $wheres[] = 'f.name LIKE :name';
            $params['name'] = '%' . $filters['name'] . '%';
        }

        if (!empty($filters['city'])) {
            $wheres[] = 'f.city = :city';
            $params['city'] = $filters['city'];
        }

        $activityTypes = [];
        if (!empty($filters['activity_types']) && is_array($filters['activity_types'])) {
            foreach ($filters['activity_types'] as $t) {
                $t = trim((string)$t);
                if ($t !== '') {
                    $activityTypes[] = $t;
                }
            }
        }
        $hasActivityType = count($activityTypes) > 0;
        $hasAvailabilityDate = !empty($filters['availability_date']);
        $hasMinPrice = array_key_exists('min_price', $filters) && $filters['min_price'] !== '';
        $hasMaxPrice = array_key_exists('max_price', $filters) && $filters['max_price'] !== '';

        $needActivityJoin = $hasActivityType || $hasAvailabilityDate || $hasMinPrice || $hasMaxPrice;
        if ($needActivityJoin) {
            $joins .= ' JOIN activities a ON a.farm_id = f.id AND a.is_active = 1 ';
        }

        if ($hasActivityType && $needActivityJoin) {
            $placeholders = [];
            foreach ($activityTypes as $i => $t) {
                $key = 'activity_type_' . $i;
                $placeholders[] = ':' . $key;
                $params[$key] = $t;
            }
            $wheres[] = 'a.activity_type IN (' . implode(',', $placeholders) . ')';
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

        if ($sortByDistance) {
            $distanceExpr = '(CASE
                WHEN f.latitude IS NULL OR f.longitude IS NULL THEN NULL
                ELSE (6371 * ACOS(
                    COS(RADIANS(:user_lat)) * COS(RADIANS(f.latitude)) *
                    COS(RADIANS(f.longitude) - RADIANS(:user_lng)) +
                    SIN(RADIANS(:user_lat2)) * SIN(RADIANS(f.latitude))
                ))
            END)';
            $selectExtra = ', ' . $distanceExpr . ' AS distance_km';
            $orderBy = 'ORDER BY (distance_km IS NULL) ASC, distance_km ASC, f.created_at DESC';
            $params['user_lat'] = $userLat;
            $params['user_lat2'] = $userLat;
            $params['user_lng'] = $userLng;
        } else {
            $selectExtra = '';
            $orderBy = 'ORDER BY f.created_at DESC';
        }

        $sql = 'SELECT DISTINCT f.*, u.name AS owner_name' . $selectExtra . '
                FROM farms f
                JOIN users u ON f.owner_id = u.id
                ' . $joins . '
                WHERE ' . implode(' AND ', $wheres) . '
                ' . $orderBy . '
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

    // -------- Reviews --------

    public function addReview(int $userId, int $farmId, int $rating, string $comment): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO reviews (user_id, farm_id, rating, comment, created_at)
             VALUES (:user_id, :farm_id, :rating, :comment, NOW())'
        );
        $stmt->execute([
            'user_id' => $userId,
            'farm_id' => $farmId,
            'rating' => $rating,
            'comment' => $comment,
        ]);

        $ownerStmt = $this->db->prepare('SELECT owner_id FROM farms WHERE id = :id');
        $ownerStmt->execute(['id' => $farmId]);
        $ownerId = (int)($ownerStmt->fetchColumn() ?: 0);
        if ($ownerId > 0) {
            $notif = new Notification();
            $notif->create($ownerId, 'review_new', 'You have a new review on your farm.');
        }
    }

    public function deleteReview(int $userId, int $farmId): void
    {
        $stmt = $this->db->prepare(
            'DELETE FROM reviews WHERE user_id = :user_id AND farm_id = :farm_id'
        );
        $stmt->execute(['user_id' => $userId, 'farm_id' => $farmId]);
    }

    public function getUserReview(int $userId, int $farmId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, user_id, farm_id, rating, comment, owner_reply, owner_reply_at, created_at
             FROM reviews
             WHERE user_id = :user_id AND farm_id = :farm_id
             LIMIT 1'
        );
        $stmt->execute(['user_id' => $userId, 'farm_id' => $farmId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function getReviewsByFarm(int $farmId): array
    {
        $stmt = $this->db->prepare(
            'SELECT r.id, r.user_id, r.rating, r.comment, r.created_at,
                    r.owner_reply, r.owner_reply_at,
                    u.name AS reviewer_name
             FROM reviews r
             JOIN users u ON u.id = r.user_id
             WHERE r.farm_id = :farm_id
             ORDER BY r.created_at DESC'
        );
        $stmt->execute(['farm_id' => $farmId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addOwnerReply(int $farmId, int $reviewId, int $ownerId, string $reply): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE reviews r
             JOIN farms f ON f.id = r.farm_id
             SET r.owner_reply = :reply, r.owner_reply_at = CURRENT_TIMESTAMP
             WHERE r.id = :review_id
               AND r.farm_id = :farm_id
               AND f.owner_id = :owner_id'
        );
        $stmt->execute([
            'reply'     => $reply,
            'review_id' => $reviewId,
            'farm_id'   => $farmId,
            'owner_id'  => $ownerId,
        ]);
        if ($stmt->rowCount() === 0) {
            return false;
        }

        $visitorStmt = $this->db->prepare('SELECT user_id FROM reviews WHERE id = :id');
        $visitorStmt->execute(['id' => $reviewId]);
        $visitorId = (int)($visitorStmt->fetchColumn() ?: 0);
        if ($visitorId > 0) {
            $notif = new Notification();
            $notif->create($visitorId, 'review_reply', 'You received a reply to your review.');
        }
        return true;
    }

    public function deleteOwnerReply(int $farmId, int $reviewId, int $ownerId): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE reviews r
             JOIN farms f ON f.id = r.farm_id
             SET r.owner_reply = NULL, r.owner_reply_at = NULL
             WHERE r.id = :review_id
               AND r.farm_id = :farm_id
               AND f.owner_id = :owner_id'
        );
        $stmt->execute([
            'review_id' => $reviewId,
            'farm_id'   => $farmId,
            'owner_id'  => $ownerId,
        ]);
        return $stmt->rowCount() > 0;
    }

    public function deleteReviewAsAdmin(int $reviewId): bool
    {
        $stmt = $this->db->prepare('DELETE FROM reviews WHERE id = :id');
        $stmt->execute(['id' => $reviewId]);
        return $stmt->rowCount() > 0;
    }

    public function getRatingSummary(int $farmId): array
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) AS count, AVG(rating) AS average
             FROM reviews
             WHERE farm_id = :farm_id'
        );
        $stmt->execute(['farm_id' => $farmId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return [
            'count' => (int)($row['count'] ?? 0),
            'average' => $row && $row['average'] !== null ? (float)$row['average'] : 0.0,
        ];
    }

    public function getRatingsForFarmIds(array $farmIds): array
    {
        if (empty($farmIds)) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($farmIds), '?'));
        $stmt = $this->db->prepare(
            "SELECT farm_id, COUNT(*) AS count, AVG(rating) AS average
             FROM reviews
             WHERE farm_id IN ($placeholders)
             GROUP BY farm_id"
        );
        $stmt->execute(array_map('intval', $farmIds));
        $out = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $out[(int)$row['farm_id']] = [
                'count' => (int)$row['count'],
                'average' => (float)$row['average'],
            ];
        }
        return $out;
    }
}


<?php
declare(strict_types=1);

namespace Controllers;

use Core\BaseController;
use Core\Cities;
use Core\Security;
use Models\Farm;

class FarmController extends BaseController
{
    public function index(): void
    {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 9;
        $offset = ($page - 1) * $perPage;

        $farmModel = new Farm();

        $userLat = $_GET['user_lat'] ?? null;
        $userLng = $_GET['user_lng'] ?? null;
        $sortedByDistance = false;

        if (is_numeric($userLat) && is_numeric($userLng)) {
            $lat = (float)$userLat;
            $lng = (float)$userLng;
            if ($lat >= -90 && $lat <= 90 && $lng >= -180 && $lng <= 180) {
                $farms = $farmModel->getPaginatedSortedByDistance($lat, $lng, $perPage, $offset);
                $sortedByDistance = true;
            } else {
                $farms = $farmModel->getPaginated($perPage, $offset);
            }
        } else {
            $farms = $farmModel->getPaginated($perPage, $offset);
        }

        $total = $farmModel->countActive();
        $totalPages = (int)ceil($total / $perPage);

        $farmIds = array_map(static fn ($f) => (int)$f['id'], $farms);
        $visitCounts = $farmModel->getVisitCountsByFarmIds($farmIds);

        $favoriteFarmIds = [];
        $user = $this->user();
        if ($user !== null && ($user['role'] ?? '') === 'visitor' && !empty($farmIds)) {
            $favoriteFarmIds = $farmModel->getFavoriteFarmIdsForUser((int)$user['id'], $farmIds);
        }

        $this->view('farm/index', [
            'farms' => $farms,
            'page' => $page,
            'totalPages' => $totalPages,
            'sortedByDistance' => $sortedByDistance,
            'cities' => Cities::LIST,
            'visitCounts' => $visitCounts,
            'favoriteFarmIds' => $favoriteFarmIds,
        ]);
    }

    public function show(): void
    {
        $farmId = (int)($_GET['farm_id'] ?? 0);
        if ($farmId <= 0) {
            http_response_code(422);
            echo 'farm_id is required.';
            return;
        }

        $farmModel = new Farm();
        $farm = $farmModel->getById($farmId);
        if (!$farm) {
            http_response_code(404);
            echo 'Farm not found.';
            return;
        }

        // Record a unique visit for logged-in visitors only.
        $user = $this->user();
        if ($user !== null && ($user['role'] ?? '') === 'visitor') {
            $farmModel->recordVisit($farmId, (int)$user['id']);
        }

        $visitCount = $farmModel->countVisits($farmId);

        // Activities for booking
        $activityModel = new \Models\Activity();
        $activities = $activityModel->getActiveByFarmId($farmId);

        $gallery = $farmModel->getGalleryImages($farmId);

        $isFavorite = false;
        if ($user !== null && ($user['role'] ?? '') === 'visitor') {
            $isFavorite = $farmModel->isFavorite((int)$user['id'], $farmId);
        }

        $this->view('farm/view', [
            'farm' => $farm,
            'activities' => $activities,
            'gallery' => $gallery,
            'visitCount' => $visitCount,
            'isFavorite' => $isFavorite,
        ]);
    }

    public function search(): void
    {
        Security::requireCsrfToken();
        header('Content-Type: application/json');

        $name = trim((string)($_POST['name'] ?? ''));
        $city = trim((string)($_POST['city'] ?? ''));
        $activityType = trim((string)($_POST['activity_type'] ?? ''));
        $availabilityDate = trim((string)($_POST['availability_date'] ?? ''));
        $minPrice = $_POST['min_price'] ?? '';
        $maxPrice = $_POST['max_price'] ?? '';

        // Ignore any city value that isn't in our fixed list.
        if ($city !== '' && !Cities::isValid($city)) {
            $city = '';
        }

        $page = max(1, (int)($_POST['page'] ?? 1));
        $perPage = 9;
        $offset = ($page - 1) * $perPage;

        $filters = [
            'name' => $name,
            'city' => $city,
            'activity_type' => $activityType,
            'availability_date' => $availabilityDate,
            'min_price' => $minPrice,
            'max_price' => $maxPrice,
        ];

        $farmModel = new Farm();
        $farms = $farmModel->searchFiltered($filters, $perPage, $offset);

        $farmIds = array_map(static fn ($f) => (int)$f['id'], $farms);
        $visitCounts = $farmModel->getVisitCountsByFarmIds($farmIds);
        foreach ($farms as &$farm) {
            $farm['visit_count'] = $visitCounts[(int)$farm['id']] ?? 0;
        }
        unset($farm);

        echo json_encode(['success' => true, 'farms' => $farms, 'page' => $page]);
    }

    public function toggleFavorite(): void
    {
        Security::requireCsrfToken();
        header('Content-Type: application/json');

        $this->requireRole(['visitor']);
        $user = $this->user();

        $farmId = (int)($_POST['farm_id'] ?? 0);
        if ($farmId <= 0) {
            http_response_code(422);
            echo json_encode(['success' => false, 'error' => 'farm_id is required.']);
            return;
        }

        $farmModel = new Farm();
        $farm = $farmModel->getById($farmId);
        if (!$farm) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Farm not found.']);
            return;
        }

        $userId = (int)$user['id'];
        $isFav = $farmModel->isFavorite($userId, $farmId);
        if ($isFav) {
            $farmModel->removeFavorite($userId, $farmId);
            $nowFav = false;
        } else {
            $farmModel->addFavorite($userId, $farmId);
            $nowFav = true;
        }

        echo json_encode(['success' => true, 'favorited' => $nowFav]);
    }

    public function favorites(): void
    {
        $this->requireRole(['visitor']);
        $user = $this->user();

        $farmModel = new Farm();
        $farms = $farmModel->getFavoritesByUser((int)$user['id']);

        $this->view('visitor/favorites', [
            'farms' => $farms,
        ]);
    }
}


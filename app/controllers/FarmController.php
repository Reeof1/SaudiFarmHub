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

        $this->view('farm/index', [
            'farms' => $farms,
            'page' => $page,
            'totalPages' => $totalPages,
            'sortedByDistance' => $sortedByDistance,
            'cities' => Cities::LIST,
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

        // Activities for booking
        $activityModel = new \Models\Activity();
        $activities = $activityModel->getActiveByFarmId($farmId);

        $gallery = $farmModel->getGalleryImages($farmId);

        $this->view('farm/view', [
            'farm' => $farm,
            'activities' => $activities,
            'gallery' => $gallery,
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

        echo json_encode(['success' => true, 'farms' => $farms, 'page' => $page]);
    }
}


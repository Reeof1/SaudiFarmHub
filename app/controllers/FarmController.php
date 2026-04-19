<?php
declare(strict_types=1);

namespace Controllers;

use Core\BaseController;
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
        $farms = $farmModel->getPaginated($perPage, $offset);
        $total = $farmModel->countActive();
        $totalPages = (int)ceil($total / $perPage);

        $this->view('farm/index', [
            'farms' => $farms,
            'page' => $page,
            'totalPages' => $totalPages,
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

        $this->view('farm/view', [
            'farm' => $farm,
            'activities' => $activities,
        ]);
    }

    public function search(): void
    {
        Security::requireCsrfToken();
        header('Content-Type: application/json');

        $name = trim((string)($_POST['name'] ?? ''));
        $location = trim((string)($_POST['location'] ?? ''));
        $activityType = trim((string)($_POST['activity_type'] ?? ''));
        $availabilityDate = trim((string)($_POST['availability_date'] ?? ''));
        $minPrice = $_POST['min_price'] ?? '';
        $maxPrice = $_POST['max_price'] ?? '';

        $page = max(1, (int)($_POST['page'] ?? 1));
        $perPage = 9;
        $offset = ($page - 1) * $perPage;

        $filters = [
            'name' => $name,
            'location' => $location,
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


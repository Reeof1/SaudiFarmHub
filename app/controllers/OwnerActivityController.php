<?php
declare(strict_types=1);

namespace Controllers;

use Core\ActivityTypes;
use Core\BaseController;
use Core\Security;
use Models\Activity;
use Models\Farm;

class OwnerActivityController extends BaseController
{
    public function index(): void
    {
        $this->requireRole(['owner']);
        $user = $this->user();
        $farmId = (int)($_GET['farm_id'] ?? 0);

        $farmModel = new Farm();
        $farm = $farmModel->getByIdForOwner($farmId, (int)$user['id']);
        if (!$farm) {
            http_response_code(404);
            echo 'Farm not found.';
            return;
        }

        $activityModel = new Activity();
        $activities = $activityModel->getActiveByFarmId($farmId);

        $this->view('owner/activities/index', [
            'farm' => $farm,
            'activities' => $activities,
        ]);
    }

    public function create(): void
    {
        $this->requireRole(['owner']);
        $user = $this->user();
        $farmId = (int)($_GET['farm_id'] ?? 0);

        $farmModel = new Farm();
        $farm = $farmModel->getByIdForOwner($farmId, (int)$user['id']);
        if (!$farm) {
            http_response_code(404);
            echo 'Farm not found.';
            return;
        }

        $this->view('owner/activities/create', ['farm' => $farm]);
    }

    public function store(): void
    {
        $this->requireRole(['owner']);
        Security::requireCsrfToken();

        $user = $this->user();
        $farmId = (int)($_POST['farm_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $type = trim($_POST['activity_type'] ?? '');
        $description = trim($_POST['description'] ?? '');

        $farmModel = new Farm();
        $farm = $farmModel->getByIdForOwner($farmId, (int)$user['id']);
        if (!$farm) {
            http_response_code(404);
            echo 'Farm not found.';
            return;
        }

        if ($name === '' || $type === '' || $description === '') {
            $this->view('owner/activities/create', [
                'farm' => $farm,
                'error' => 'All fields are required.',
            ]);
            return;
        }

        if (!ActivityTypes::isValid($type)) {
            $this->view('owner/activities/create', [
                'farm' => $farm,
                'error' => 'Please choose a valid activity type from the list.',
            ]);
            return;
        }

        $activityModel = new Activity();
        $activityModel->create([
            'farm_id' => $farmId,
            'name' => $name,
            'activity_type' => $type,
            'description' => $description,
        ]);

        $this->redirect('owner/farm/activities?farm_id=' . $farmId);
    }

    public function edit(): void
    {
        $this->requireRole(['owner']);
        $user = $this->user();
        $id = (int)($_GET['id'] ?? 0);

        $activityModel = new Activity();
        $activity = $activityModel->getByIdForOwner($id, (int)$user['id']);
        if (!$activity) {
            http_response_code(404);
            echo 'Activity not found.';
            return;
        }

        $this->view('owner/activities/edit', ['activity' => $activity]);
    }

    public function update(): void
    {
        $this->requireRole(['owner']);
        Security::requireCsrfToken();

        $user = $this->user();
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $type = trim($_POST['activity_type'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if ($id <= 0 || $name === '' || $type === '' || $description === '') {
            http_response_code(422);
            echo 'Invalid activity data.';
            return;
        }

        $activityModel = new Activity();
        $activity = $activityModel->getByIdForOwner($id, (int)$user['id']);
        if (!$activity) {
            http_response_code(404);
            echo 'Activity not found.';
            return;
        }

        if (!ActivityTypes::isValid($type) && $type !== (string)$activity['activity_type']) {
            http_response_code(422);
            echo 'Please choose a valid activity type from the list.';
            return;
        }

        $activityModel->update($id, [
            'name' => $name,
            'activity_type' => $type,
            'description' => $description,
        ]);

        $this->redirect('owner/farm/activities?farm_id=' . (int)$activity['farm_id']);
    }

    public function delete(): void
    {
        $this->requireRole(['owner']);
        Security::requireCsrfToken();

        $user = $this->user();
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            $this->redirect('owner/farms');
        }

        $activityModel = new Activity();
        $activity = $activityModel->getByIdForOwner($id, (int)$user['id']);
        if (!$activity) {
            $this->redirect('owner/farms');
        }

        $activityModel->softDelete($id);
        $this->redirect('owner/farm/activities?farm_id=' . (int)$activity['farm_id']);
    }
}


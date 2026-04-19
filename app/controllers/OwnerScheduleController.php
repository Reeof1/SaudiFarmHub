<?php
declare(strict_types=1);

namespace Controllers;

use Core\BaseController;
use Core\Security;
use Models\Activity;
use Models\Farm;
use Models\Schedule;

class OwnerScheduleController extends BaseController
{
    public function index(): void
    {
        $this->requireRole(['owner']);
        $user = $this->user();
        $activityId = (int)($_GET['activity_id'] ?? 0);

        $activityModel = new Activity();
        $activity = $activityModel->getByIdForOwner($activityId, (int)$user['id']);
        if (!$activity) {
            http_response_code(404);
            echo 'Activity not found.';
            return;
        }

        $scheduleModel = new Schedule();
        $schedules = $scheduleModel->getByActivityForOwner($activityId, (int)$user['id']);

        $this->view('owner/schedules/index', [
            'activity' => $activity,
            'schedules' => $schedules,
        ]);
    }

    public function create(): void
    {
        $this->requireRole(['owner']);
        $user = $this->user();
        $activityId = (int)($_GET['activity_id'] ?? 0);

        $activityModel = new Activity();
        $activity = $activityModel->getByIdForOwner($activityId, (int)$user['id']);
        if (!$activity) {
            http_response_code(404);
            echo 'Activity not found.';
            return;
        }

        $this->view('owner/schedules/create', ['activity' => $activity]);
    }

    public function store(): void
    {
        $this->requireRole(['owner']);
        Security::requireCsrfToken();

        $user = $this->user();
        $activityId = (int)($_POST['activity_id'] ?? 0);
        $date = trim($_POST['schedule_date'] ?? '');
        $start = trim($_POST['start_time'] ?? '');
        $end = trim($_POST['end_time'] ?? '');
        $capacity = (int)($_POST['capacity'] ?? 0);
        $price = (float)($_POST['price'] ?? 0);

        $activityModel = new Activity();
        $activity = $activityModel->getByIdForOwner($activityId, (int)$user['id']);
        if (!$activity) {
            http_response_code(404);
            echo 'Activity not found.';
            return;
        }

        if ($date === '' || $start === '' || $end === '' || $capacity <= 0 || $price < 0) {
            $this->view('owner/schedules/create', [
                'activity' => $activity,
                'error' => 'All fields are required and must be valid.',
            ]);
            return;
        }

        $scheduleModel = new Schedule();
        $ok = $scheduleModel->createForActivity($activityId, [
            'schedule_date' => $date,
            'start_time' => $start,
            'end_time' => $end,
            'capacity' => $capacity,
            'price' => $price,
        ]);

        if (!$ok) {
            $this->view('owner/schedules/create', [
                'activity' => $activity,
                'error' => 'Could not create schedule (maybe duplicate time?).',
            ]);
            return;
        }

        $this->redirect('owner/farm/schedules?activity_id=' . $activityId);
    }

    public function edit(): void
    {
        $this->requireRole(['owner']);
        $user = $this->user();
        $id = (int)($_GET['id'] ?? 0);

        $scheduleModel = new Schedule();
        $schedule = $scheduleModel->getByIdForOwner($id, (int)$user['id']);
        if (!$schedule) {
            http_response_code(404);
            echo 'Schedule not found.';
            return;
        }

        $this->view('owner/schedules/edit', ['schedule' => $schedule]);
    }

    public function update(): void
    {
        $this->requireRole(['owner']);
        Security::requireCsrfToken();

        $user = $this->user();
        $id = (int)($_POST['id'] ?? 0);
        $date = trim($_POST['schedule_date'] ?? '');
        $start = trim($_POST['start_time'] ?? '');
        $end = trim($_POST['end_time'] ?? '');
        $capacity = (int)($_POST['capacity'] ?? 0);
        $price = (float)($_POST['price'] ?? 0);

        if ($id <= 0 || $date === '' || $start === '' || $end === '' || $capacity <= 0 || $price < 0) {
            http_response_code(422);
            echo 'Invalid schedule data.';
            return;
        }

        $scheduleModel = new Schedule();
        $schedule = $scheduleModel->getByIdForOwner($id, (int)$user['id']);
        if (!$schedule) {
            http_response_code(404);
            echo 'Schedule not found.';
            return;
        }

        $scheduleModel->update($id, [
            'schedule_date' => $date,
            'start_time' => $start,
            'end_time' => $end,
            'capacity' => $capacity,
            'price' => $price,
        ]);

        $this->redirect('owner/farm/schedules?activity_id=' . (int)$schedule['activity_id']);
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

        $scheduleModel = new Schedule();
        $schedule = $scheduleModel->getByIdForOwner($id, (int)$user['id']);
        if (!$schedule) {
            $this->redirect('owner/farms');
        }

        $scheduleModel->softDelete($id);
        $this->redirect('owner/farm/schedules?activity_id=' . (int)$schedule['activity_id']);
    }
}


<?php
declare(strict_types=1);

namespace Controllers;

use Core\BaseController;
use Core\Security;
use Models\Booking;

class BookingController extends BaseController
{
    public function availability(): void
    {
        if (!$this->isLoggedIn()) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Login required.']);
            return;
        }

        $this->requireRole(['visitor']);

        $activityId = (int)($_GET['activity_id'] ?? 0);
        $date = trim((string)($_GET['date'] ?? ''));
        if ($activityId <= 0 || $date === '') {
            http_response_code(422);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'activity_id and date are required.']);
            return;
        }

        header('Content-Type: application/json');
        $scheduleModel = new \Models\Schedule();
        $bookingsModel = new Booking();
        $schedules = $scheduleModel->getAvailableByActivityDate($activityId, $date);

        $out = [];
        foreach ($schedules as $s) {
            $bookedTotal = $bookingsModel->getBookedTotalForSchedule((int)$s['id']);
            $capacity = (int)($s['capacity'] ?? 0);
            $remaining = max(0, $capacity - $bookedTotal);
            $out[] = [
                'id' => (int)$s['id'],
                'start_time' => (string)$s['start_time'],
                'end_time' => (string)$s['end_time'],
                'price' => (string)$s['price'],
                'capacity' => $capacity,
                'remaining' => $remaining,
            ];
        }

        echo json_encode(['success' => true, 'schedules' => $out]);
    }

    public function create(): void
    {
        $this->requireRole(['visitor']);
        Security::requireCsrfToken();

        header('Content-Type: application/json');
        $visitorId = (int)($this->user()['id'] ?? 0);
        $scheduleId = (int)($_POST['schedule_id'] ?? 0);
        $partySize = (int)($_POST['party_size'] ?? 1);

        if ($scheduleId <= 0) {
            http_response_code(422);
            echo json_encode(['success' => false, 'error' => 'schedule_id is required.']);
            return;
        }

        try {
            $bookingModel = new Booking();
            $booking = $bookingModel->createBooking($visitorId, $scheduleId, $partySize);
            echo json_encode(['success' => true, 'booking' => $booking]);
        } catch (\Throwable $e) {
            http_response_code(409);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function updateStatus(): void
    {
        $this->requireRole(['owner', 'admin']);
        Security::requireCsrfToken();

        header('Content-Type: application/json');
        $actor = $this->user();
        $actorId = (int)($actor['id'] ?? 0);
        $actorRole = (string)($actor['role'] ?? '');
        $bookingId = (int)($_POST['booking_id'] ?? 0);
        $status = trim((string)($_POST['status'] ?? ''));
        $status = ucfirst(strtolower($status));

        if ($bookingId <= 0 || !in_array($status, ['Approved', 'Completed'], true)) {
            http_response_code(422);
            echo json_encode(['success' => false, 'error' => 'Invalid booking_id or status.']);
            return;
        }

        try {
            $bookingModel = new Booking();
            $result = $bookingModel->updateStatus($bookingId, $status, $actorId, $actorRole);
            echo json_encode(['success' => true, 'booking' => $result]);
        } catch (\Throwable $e) {
            http_response_code(409);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function cancel(): void
    {
        $this->requireRole(['visitor', 'owner', 'admin']);
        Security::requireCsrfToken();

        header('Content-Type: application/json');
        $actor = $this->user();
        $actorId = (int)($actor['id'] ?? 0);
        $actorRole = (string)($actor['role'] ?? '');
        $bookingId = (int)($_POST['booking_id'] ?? 0);

        if ($bookingId <= 0) {
            http_response_code(422);
            echo json_encode(['success' => false, 'error' => 'booking_id is required.']);
            return;
        }

        try {
            $bookingModel = new Booking();
            $result = $bookingModel->cancelBooking($bookingId, $actorId, $actorRole);
            echo json_encode(['success' => true, 'booking' => $result]);
        } catch (\Throwable $e) {
            http_response_code(409);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}


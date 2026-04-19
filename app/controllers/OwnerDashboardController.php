<?php
declare(strict_types=1);

namespace Controllers;

use Core\BaseController;
use Models\Booking;
use Models\Farm;
use Models\Notification;

class OwnerDashboardController extends BaseController
{
    public function index(): void
    {
        $this->requireRole(['owner']);
        $user = $this->user();

        $farmModel = new Farm();
        $bookingModel = new Booking();
        $notifModel = new Notification();

        $farmCount = $farmModel->countByOwnerId((int)$user['id']);
        $statusCounts = $bookingModel->getStatusCountsForOwner((int)$user['id']);
        $unread = $notifModel->unreadCount((int)$user['id']);

        $this->view('dashboard/owner/index', [
            'farmCount' => $farmCount,
            'statusCounts' => $statusCounts,
            'unreadNotifications' => $unread,
        ]);
    }

    public function bookings(): void
    {
        $this->requireRole(['owner']);
        $user = $this->user();

        $bookingModel = new Booking();
        $bookings = $bookingModel->getForOwner((int)$user['id']);

        $this->view('dashboard/owner/bookings', [
            'bookings' => $bookings,
        ]);
    }
}


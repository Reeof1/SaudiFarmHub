<?php
declare(strict_types=1);

namespace Controllers;

use Core\BaseController;
use Models\Booking;
use Models\Notification;

class VisitorDashboardController extends BaseController
{
    public function index(): void
    {
        $this->requireRole(['visitor']);
        $user = $this->user();
        $bookingModel = new Booking();
        $notifModel = new Notification();

        $statusCounts = $bookingModel->getStatusCountsForVisitor((int)$user['id']);
        $unread = $notifModel->unreadCount((int)$user['id']);

        $this->view('dashboard/visitor/index', [
            'statusCounts' => $statusCounts,
            'unreadNotifications' => $unread,
        ]);
    }

    public function bookings(): void
    {
        $this->requireRole(['visitor']);
        $user = $this->user();

        $bookingModel = new Booking();
        $bookings = $bookingModel->getForVisitor((int)$user['id']);

        $this->view('dashboard/visitor/bookings', [
            'bookings' => $bookings,
        ]);
    }
}


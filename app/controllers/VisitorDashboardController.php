<?php
declare(strict_types=1);

namespace Controllers;

use Core\BaseController;
use Models\Booking;
use Models\Farm;
use Models\Notification;

class VisitorDashboardController extends BaseController
{
    public function index(): void
    {
        $this->requireRole(['visitor']);
        $user = $this->user();
        $bookingModel = new Booking();
        $notifModel = new Notification();
        $farmModel = new Farm();

        $statusCounts = $bookingModel->getStatusCountsForVisitor((int)$user['id']);
        $unread = $notifModel->unreadCount((int)$user['id']);
        $favoritesCount = $farmModel->countFavoritesByUser((int)$user['id']);

        $this->view('dashboard/visitor/index', [
            'statusCounts' => $statusCounts,
            'unreadNotifications' => $unread,
            'favoritesCount' => $favoritesCount,
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


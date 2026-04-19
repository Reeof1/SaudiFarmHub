<?php
declare(strict_types=1);

namespace Controllers;

use Core\BaseController;
use Models\Booking;
use Models\Farm;

class AdminDashboardController extends BaseController
{
    public function index(): void
    {
        $this->requireRole(['admin']);
        $bookingModel = new Booking();
        $bookings = $bookingModel->getAllForAdmin();
        $bookingCounts = $bookingModel->getStatusCountsForAdmin();

        $farmModel = new Farm();
        $pendingFarms = $farmModel->countByApprovalStatus('pending');

        $this->view('dashboard/admin/index', [
            'bookings' => $bookings,
            'bookingCounts' => $bookingCounts,
            'pendingFarms' => $pendingFarms,
        ]);
    }
}


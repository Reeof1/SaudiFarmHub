<?php
declare(strict_types=1);

namespace Controllers;

use Core\BaseController;
use Core\Security;
use Models\Booking;
use Models\Farm;
use Models\Report;
use Models\User;

class AdminReportController extends BaseController
{
    public function index(): void
    {
        $this->requireRole(['admin']);
        $reportModel = new Report();
        $reports = $reportModel->getAll();
        $this->view('admin/reports/index', ['reports' => $reports]);
    }

    public function generate(): void
    {
        $this->requireRole(['admin']);
        Security::requireCsrfToken();

        $type = trim((string)($_POST['report_type'] ?? 'system_summary'));
        $allowed = ['system_summary', 'bookings_summary', 'farms_summary', 'users_summary'];
        if (!in_array($type, $allowed, true)) {
            http_response_code(422);
            echo 'Invalid report type.';
            return;
        }

        $adminId = (int)($this->user()['id'] ?? 0);
        $payload = $this->buildPayload($type);

        $reportModel = new Report();
        $reportModel->create($adminId, $type, $payload);

        $this->redirect('admin/reports');
    }

    private function buildPayload(string $type): array
    {
        $bookingModel = new Booking();
        $farmModel = new Farm();
        $userModel = new User();

        $users = $userModel->getAllWithRoles();
        $userCount = count($users);
        $activeCount = 0;
        foreach ($users as $u) {
            if ((int)($u['is_active'] ?? 0) === 1) {
                $activeCount++;
            }
        }

        $bookingCounts = $bookingModel->getStatusCountsForAdmin();
        $farmsApproved = $farmModel->countByApprovalStatus('approved');
        $farmsPending = $farmModel->countByApprovalStatus('pending');
        $farmsRejected = $farmModel->countByApprovalStatus('rejected');

        $base = [
            'generated_at' => date('c'),
            'bookings' => $bookingCounts,
            'farms' => [
                'approved' => $farmsApproved,
                'pending' => $farmsPending,
                'rejected' => $farmsRejected,
            ],
            'users' => [
                'total' => $userCount,
                'active' => $activeCount,
                'inactive' => max(0, $userCount - $activeCount),
            ],
        ];

        if ($type === 'bookings_summary') {
            return [
                'generated_at' => $base['generated_at'],
                'bookings' => $base['bookings'],
            ];
        }
        if ($type === 'farms_summary') {
            return [
                'generated_at' => $base['generated_at'],
                'farms' => $base['farms'],
            ];
        }
        if ($type === 'users_summary') {
            return [
                'generated_at' => $base['generated_at'],
                'users' => $base['users'],
            ];
        }
        return $base;
    }
}


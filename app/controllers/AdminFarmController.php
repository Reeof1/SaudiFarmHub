<?php
declare(strict_types=1);

namespace Controllers;

use Core\BaseController;
use Core\Security;
use Models\Farm;

class AdminFarmController extends BaseController
{
    public function index(): void
    {
        $this->requireRole(['admin']);

        $farmModel = new Farm();
        $pending = $farmModel->getByApprovalStatus('pending');
        $approved = $farmModel->getByApprovalStatus('approved');
        $rejected = $farmModel->getByApprovalStatus('rejected');

        $this->view('admin/farms/index', [
            'pending' => $pending,
            'approved' => $approved,
            'rejected' => $rejected,
        ]);
    }

    public function updateStatus(): void
    {
        $this->requireRole(['admin']);
        Security::requireCsrfToken();

        $id = (int)($_POST['id'] ?? 0);
        $status = trim((string)($_POST['status'] ?? ''));
        if ($id <= 0 || !in_array($status, ['pending', 'approved', 'rejected'], true)) {
            http_response_code(422);
            echo 'Invalid farm status update.';
            return;
        }

        $farmModel = new Farm();
        $farmModel->setApprovalStatus($id, $status);

        $this->redirect('admin/farms');
    }
}


<?php
declare(strict_types=1);

namespace Controllers;

use Core\BaseController;
use Models\Notification;

class AdminAlertController extends BaseController
{
    public function index(): void
    {
        $this->requireRole(['admin']);
        $adminId = (int)($this->user()['id'] ?? 0);
        $notifModel = new Notification();
        $alerts = $notifModel->getAlertsForUser($adminId, 50);
        $this->view('admin/alerts/index', ['alerts' => $alerts]);
    }
}


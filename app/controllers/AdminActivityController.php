<?php
declare(strict_types=1);

namespace Controllers;

use Core\BaseController;
use Models\SystemActivity;

class AdminActivityController extends BaseController
{
    public function index(): void
    {
        $this->requireRole(['admin']);
        $activityModel = new SystemActivity();
        $events = $activityModel->recent(50);
        $this->view('admin/activity/index', ['events' => $events]);
    }
}


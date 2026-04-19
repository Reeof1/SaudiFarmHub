<?php
declare(strict_types=1);

namespace Controllers;

use Core\BaseController;
use Core\Security;
use Models\Notification;

class NotificationController extends BaseController
{
    public function index(): void
    {
        if (!$this->isLoggedIn()) {
            http_response_code(403);
            echo 'Access denied.';
            return;
        }
        $user = $this->user();
        $notifModel = new Notification();
        $notifications = $notifModel->getForUser((int)$user['id'], 50, 0);
        $this->view('notifications/index', ['notifications' => $notifications]);
    }

    public function markRead(): void
    {
        $this->requireRole(['visitor', 'owner', 'admin']);
        Security::requireCsrfToken();

        header('Content-Type: application/json');
        $user = $this->user();
        $notifModel = new Notification();
        $notifModel->markAllRead((int)$user['id']);
        echo json_encode(['success' => true, 'unread_count' => $notifModel->unreadCount((int)$user['id'])]);
    }

    public function unreadCount(): void
    {
        if (!$this->isLoggedIn()) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['count' => 0]);
            return;
        }
        $user = $this->user();
        $notifModel = new Notification();
        $count = $notifModel->unreadCount((int)$user['id']);
        header('Content-Type: application/json');
        echo json_encode(['count' => $count]);
    }
}


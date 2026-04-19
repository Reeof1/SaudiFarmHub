<?php
declare(strict_types=1);

namespace Controllers;

use Core\BaseController;
use Core\Security;
use Models\User;

class AdminUserController extends BaseController
{
    public function index(): void
    {
        $this->requireRole(['admin']);
        $userModel = new User();
        $users = $userModel->getAllWithRoles();
        $this->view('admin/users/index', ['users' => $users]);
    }

    public function updateRole(): void
    {
        $this->requireRole(['admin']);
        Security::requireCsrfToken();

        $id = (int)($_POST['id'] ?? 0);
        $role = trim((string)($_POST['role'] ?? ''));
        if ($id <= 0 || !in_array($role, ['visitor', 'owner', 'admin'], true)) {
            http_response_code(422);
            echo 'Invalid role update.';
            return;
        }

        $userModel = new User();
        $userModel->updateRole($id, $role);
        $this->redirect('admin/users');
    }

    public function toggleStatus(): void
    {
        $this->requireRole(['admin']);
        Security::requireCsrfToken();

        $id = (int)($_POST['id'] ?? 0);
        $isActive = (int)($_POST['is_active'] ?? 0) === 1;
        $currentAdminId = (int)($this->user()['id'] ?? 0);

        if ($id <= 0) {
            http_response_code(422);
            echo 'Invalid user.';
            return;
        }

        // Prevent locking yourself out.
        if ($id === $currentAdminId && !$isActive) {
            $this->redirect('admin/users');
        }

        $userModel = new User();
        $userModel->setActiveStatus($id, $isActive);
        $this->redirect('admin/users');
    }
}


<?php
declare(strict_types=1);

namespace Controllers;

use Core\BaseController;
use Core\Security;
use Models\User;

class SettingsController extends BaseController
{
    public function index(): void
    {
        if (!$this->isLoggedIn()) {
            $this->redirect('login');
        }

        $user = $this->user();
        $userModel = new User();
        $fresh = $userModel->findById((int)$user['id']);

        $this->view('settings/index', [
            'user' => $fresh ?? $user,
        ]);
    }

    public function update(): void
    {
        if (!$this->isLoggedIn()) {
            $this->redirect('login');
        }
        Security::requireCsrfToken();

        $sessionUser = $this->user();
        $userId = (int)($sessionUser['id'] ?? 0);

        $name = trim((string)($_POST['name'] ?? ''));
        $email = trim((string)($_POST['email'] ?? ''));

        $currentPassword = (string)($_POST['current_password'] ?? '');
        $newPassword = (string)($_POST['new_password'] ?? '');
        $confirmPassword = (string)($_POST['confirm_password'] ?? '');

        if ($name === '' || $email === '') {
            $this->view('settings/index', [
                'user' => $sessionUser,
                'error' => 'Name and email are required.',
            ]);
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->view('settings/index', [
                'user' => $sessionUser,
                'error' => 'Please enter a valid email address.',
            ]);
            return;
        }

        $userModel = new User();
        $dbUser = $userModel->findById($userId);
        if (!$dbUser) {
            http_response_code(404);
            echo 'User not found.';
            return;
        }

        // Check email uniqueness (exclude current user).
        $existing = $userModel->findByEmail($email);
        if ($existing && (int)$existing['id'] !== $userId) {
            $this->view('settings/index', [
                'user' => $dbUser,
                'error' => 'That email is already in use.',
            ]);
            return;
        }

        // Optional password change.
        $changePassword = ($newPassword !== '' || $confirmPassword !== '' || $currentPassword !== '');
        $newHash = null;
        if ($changePassword) {
            if (!password_verify($currentPassword, (string)$dbUser['password'])) {
                $this->view('settings/index', [
                    'user' => $dbUser,
                    'error' => 'Current password is incorrect.',
                ]);
                return;
            }
            if ($newPassword === '' || strlen($newPassword) < 6) {
                $this->view('settings/index', [
                    'user' => $dbUser,
                    'error' => 'New password must be at least 6 characters.',
                ]);
                return;
            }
            if ($newPassword !== $confirmPassword) {
                $this->view('settings/index', [
                    'user' => $dbUser,
                    'error' => 'New password and confirmation do not match.',
                ]);
                return;
            }
            $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
        }

        $userModel->updateProfile($userId, [
            'name' => $name,
            'email' => $email,
            'password' => $newHash, // null means no change
        ]);

        // Refresh session display values (role unchanged).
        $_SESSION['user']['name'] = $name;
        $_SESSION['user']['email'] = $email;

        $this->view('settings/index', [
            'user' => array_merge($dbUser, ['name' => $name, 'email' => $email]),
            'success' => 'Settings updated successfully.',
        ]);
    }
}


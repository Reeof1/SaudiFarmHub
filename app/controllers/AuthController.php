<?php
declare(strict_types=1);

namespace Controllers;

use Core\BaseController;
use Core\Security;
use Models\User;

class AuthController extends BaseController
{
    public function showLogin(): void
    {
        $this->view('auth/login');
    }

    public function showRegister(): void
    {
        $this->view('auth/register');
    }

    public function login(): void
    {
        Security::requireCsrfToken();

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $userModel = new User();
        $user = $userModel->findByEmail($email);

        if (!$user || !password_verify($password, $user['password'])) {
            $this->view('auth/login', ['error' => 'Invalid credentials.']);
            return;
        }

        if (isset($user['is_active']) && (int)$user['is_active'] !== 1) {
            $this->view('auth/login', ['error' => 'Your account is deactivated. Contact administrator.']);
            return;
        }

        $_SESSION['user'] = [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
        ];

        if ($user['role'] === 'admin') {
            $this->redirect('dashboard/admin');
        } elseif ($user['role'] === 'owner') {
            $this->redirect('dashboard/owner');
        } else {
            $this->redirect('dashboard/visitor');
        }
    }

    public function register(): void
    {
        Security::requireCsrfToken();

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['password_confirmation'] ?? '';
        $role = $_POST['role'] ?? 'visitor';

        if ($password !== $confirm) {
            $this->view('auth/register', ['error' => 'Passwords do not match.']);
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->view('auth/register', ['error' => 'Invalid email.']);
            return;
        }

        $userModel = new User();
        if ($userModel->findByEmail($email)) {
            $this->view('auth/register', ['error' => 'Email already registered.']);
            return;
        }

        $hashed = password_hash($password, PASSWORD_DEFAULT);

        $id = $userModel->create([
            'name' => $name,
            'email' => $email,
            'password' => $hashed,
            'role' => $role,
        ]);

        $_SESSION['user'] = [
            'id' => $id,
            'name' => $name,
            'email' => $email,
            'role' => $role,
        ];

        if ($role === 'admin') {
            $this->redirect('dashboard/admin');
        } elseif ($role === 'owner') {
            $this->redirect('dashboard/owner');
        } else {
            $this->redirect('dashboard/visitor');
        }
    }

    public function logout(): void
    {
        session_destroy();
        $this->redirect('');
    }
}


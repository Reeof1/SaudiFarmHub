<?php
declare(strict_types=1);

namespace Core;

class BaseController
{
    protected function view(string $view, array $data = [], string $layout = 'layouts/main'): void
    {
        $viewFile = dirname(__DIR__) . '/app/views/' . $view . '.php';
        $layoutFile = dirname(__DIR__) . '/app/views/' . $layout . '.php';

        if (!file_exists($viewFile)) {
            http_response_code(500);
            echo 'View not found';
            exit;
        }

        extract($data, EXTR_SKIP);

        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        if (file_exists($layoutFile)) {
            require $layoutFile;
        } else {
            echo $content;
        }
    }

    protected function redirect(string $path): void
    {
        header('Location: ' . base_url($path));
        exit;
    }

    protected function isLoggedIn(): bool
    {
        return isset($_SESSION['user']);
    }

    protected function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    protected function requireRole(array $roles): void
    {
        if (!$this->isLoggedIn() || !in_array($this->user()['role'] ?? '', $roles, true)) {
            http_response_code(403);
            echo 'Access denied.';
            exit;
        }
    }
}


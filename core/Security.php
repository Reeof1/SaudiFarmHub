<?php
declare(strict_types=1);

namespace Core;

class Security
{
    public static function getCsrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function validateCsrfToken(?string $token): bool
    {
        return isset($_SESSION['csrf_token'], $token) &&
            hash_equals($_SESSION['csrf_token'], $token);
    }

    public static function requireCsrfToken(): void
    {
        $token = $_POST['csrf_token'] ?? null;
        if (!self::validateCsrfToken($token)) {
            http_response_code(400);
            echo 'Invalid CSRF token.';
            exit;
        }
    }
}


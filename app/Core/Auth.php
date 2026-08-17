<?php

declare(strict_types=1);

namespace App\Core;

class Auth
{
    public static function userId(): ?int
    {
        $id = $_SESSION['user_id'] ?? null;
        return is_int($id) ? $id : null;
    }

    public static function user(): ?array
    {
        $id = self::userId();
        if ($id === null) {
            return null;
        }
        return (new \App\Models\User(\App\Models\Database::connection()))->find($id);
    }

    public static function login(int $userId): void
    {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $userId;
    }

    public static function logout(): void
    {
        session_unset();
        session_destroy();
        session_start();
        session_regenerate_id(true);
    }

    public static function require(): void
    {
        if (self::userId() === null) {
            Response::redirect('index.php?route=auth.login');
        }
    }
}
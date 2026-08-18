<?php

declare(strict_types=1);

namespace App\Core;

class Response
{
    public static function e(?string $value): string
    {
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
    }

    public static function view(string $template, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        include dirname(__DIR__) . '/Views/' . $template . '.php';
    }

    public static function json(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    public static $abort; // ?callable — test seam; defaults to exit.

    public static function redirect(string $url): void
    {
        header('Location: ' . $url);
        $abort = self::$abort ?? static fn (string $_url): never => exit();
        $abort($url);
    }
}

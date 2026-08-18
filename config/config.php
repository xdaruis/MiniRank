<?php

declare(strict_types=1);

$root = dirname(__DIR__);

$defaults = [
    'database' => $root . '/database/minirank.db',
];

$env = [];

$envFile = $root . '/.env';
if (is_file($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $env[trim($key)] = trim($value);
    }
}

foreach (['DATABASE_PATH'] as $key) {
    $value = getenv($key);
    if ($value !== false && $value !== '') {
        $env[$key] = $value;
    }
}

$database = $env['DATABASE_PATH'] ?? $defaults['database'];
if (!str_starts_with($database, '/')) {
    $database = $root . '/' . $database;
}

return [
    'database' => $database,
    'site' => [
        'name' => 'MiniRank demo site',
        'url' => 'https://example.com',
    ],
];
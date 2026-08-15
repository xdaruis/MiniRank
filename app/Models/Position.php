<?php

declare(strict_types=1);

namespace App\Models;

class Position
{
    public function __construct(private \PDO $db)
    {
    }

    public function history(int $keywordId): array
    {
        // TODO: SELECT date + position for a keyword, newest first.
        return [];
    }

    public function current(int $keywordId): ?int
    {
        // TODO: SELECT latest position for a keyword.
        return null;
    }

    public function trend(int $keywordId): string
    {
        // TODO: compare 7 days ago vs today -> 'improved' | 'declined' | 'stable'.
        return 'stable';
    }

    public function refreshForToday(int $keywordId): array
    {
        // TODO: generate today's position, upsert, return [position, trend].
        return ['position' => null, 'trend' => 'stable'];
    }
}

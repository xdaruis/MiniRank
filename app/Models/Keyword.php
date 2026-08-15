<?php

declare(strict_types=1);

namespace App\Models;

class Keyword
{
    public function __construct(private \PDO $db)
    {
    }

    public function all(string $search = ''): array
    {
        // TODO: SELECT keywords with latest position; filter by search.
        return [];
    }

    public function find(int $id): ?array
    {
        // TODO: SELECT keyword by id.
        return null;
    }

    public function create(string $phrase): void
    {
        // TODO: INSERT keyword (prepared statement).
    }

    public function update(int $id, string $phrase): void
    {
        // TODO: UPDATE keyword (prepared statement).
    }

    public function delete(int $id): void
    {
        // TODO: DELETE keyword (cascade removes positions).
    }
}

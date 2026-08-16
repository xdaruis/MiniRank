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
        $sql = 'SELECT id, phrase, created_at FROM keywords ORDER BY id';
        return $this->db->query($sql)->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT id, phrase, created_at FROM keywords WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public function create(string $phrase): int
    {
        $stmt = $this->db->prepare('INSERT INTO keywords (phrase) VALUES (:phrase)');
        $stmt->execute(['phrase' => $phrase]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, string $phrase): void
    {
        $stmt = $this->db->prepare('UPDATE keywords SET phrase = :phrase WHERE id = :id');
        $stmt->execute(['id' => $id, 'phrase' => $phrase]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM keywords WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}

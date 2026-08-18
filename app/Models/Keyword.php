<?php

declare(strict_types=1);

namespace App\Models;

class Keyword
{
    public function __construct(private \PDO $db)
    {
    }

    public function all(int $userId, int $projectId, string $search = ''): array
    {
        $sql = 'SELECT k.id, k.phrase, k.created_at,
                       (SELECT p.position FROM positions p
                         WHERE p.keyword_id = k.id
                         ORDER BY p.captured_at DESC, p.id DESC LIMIT 1) AS position
                FROM keywords k
                JOIN projects pr ON pr.id = k.project_id
                WHERE pr.user_id = :user_id AND k.project_id = :project_id
                  AND (:search = \'\' OR lower(k.phrase) LIKE lower(:pattern))
                ORDER BY k.id';

        $stmt = $this->db->prepare($sql);
        $pattern = '%' . $search . '%';
        $stmt->execute([
            'user_id' => $userId,
            'project_id' => $projectId,
            'search' => $search,
            'pattern' => $pattern,
        ]);
        return $stmt->fetchAll();
    }

    public function idsForUser(int $userId, int $projectId): array
    {
        $sql = 'SELECT k.id FROM keywords k
                JOIN projects pr ON pr.id = k.project_id
                WHERE pr.user_id = :user_id AND k.project_id = :project_id';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId, 'project_id' => $projectId]);
        return $stmt->fetchAll();
    }

    public function findOwned(int $userId, int $id): ?array
    {
        $sql = 'SELECT k.id, k.phrase, k.created_at
                FROM keywords k
                JOIN projects pr ON pr.id = k.project_id
                WHERE k.id = :id AND pr.user_id = :user_id';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id, 'user_id' => $userId]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public function create(int $projectId, string $phrase): int
    {
        $stmt = $this->db->prepare('INSERT INTO keywords (project_id, phrase) VALUES (:project_id, :phrase)');
        $stmt->execute(['project_id' => $projectId, 'phrase' => $phrase]);
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
<?php

declare(strict_types=1);

namespace App\Models;

class Project
{
    public function __construct(private \PDO $db)
    {
    }

    public function userProjects(int $userId): array
    {
        $stmt = $this->db->prepare('SELECT id, domain FROM projects WHERE user_id = :user_id ORDER BY id');
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function owns(int $userId, int $projectId): bool
    {
        $stmt = $this->db->prepare(
            'SELECT 1 FROM projects WHERE id = :project_id AND user_id = :user_id'
        );
        $stmt->execute(['project_id' => $projectId, 'user_id' => $userId]);
        return $stmt->fetchColumn() !== false;
    }

    public function firstFor(int $userId): ?array
    {
        $stmt = $this->db->prepare('SELECT id, domain FROM projects WHERE user_id = :user_id ORDER BY id LIMIT 1');
        $stmt->execute(['user_id' => $userId]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public function create(int $userId, string $domain): int
    {
        $stmt = $this->db->prepare('INSERT INTO projects (user_id, domain) VALUES (:user_id, :domain)');
        $stmt->execute(['user_id' => $userId, 'domain' => $domain]);
        return (int) $this->db->lastInsertId();
    }
}
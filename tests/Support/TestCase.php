<?php

declare(strict_types=1);

namespace App\Tests\Support;

use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected \PDO $pdo;
    private string $dbFile;

    protected function setUp(): void
    {
        $this->dbFile = sys_get_temp_dir() . '/minirank_case_' . bin2hex(random_bytes(6)) . '.db';
        $this->pdo = new \PDO('sqlite:' . $this->dbFile);
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
        $this->pdo->exec('PRAGMA foreign_keys = ON');
        $this->pdo->exec(file_get_contents(dirname(__DIR__, 2) . '/database/schema.sql'));
    }

    protected function tearDown(): void
    {
        unset($this->pdo);
        if (isset($this->dbFile) && is_file($this->dbFile)) {
            unlink($this->dbFile);
        }
    }

    protected function seedUser(string $username = 'alice'): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO users (username, password_hash) VALUES (:username, :password_hash)'
        );
        $stmt->execute(['username' => $username, 'password_hash' => password_hash('password', PASSWORD_DEFAULT)]);
        return (int) $this->pdo->lastInsertId();
    }

    protected function seedProject(int $userId, string $domain): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO projects (user_id, domain) VALUES (:user_id, :domain)');
        $stmt->execute(['user_id' => $userId, 'domain' => $domain]);
        return (int) $this->pdo->lastInsertId();
    }

    protected function seedKeyword(int $projectId, string $phrase): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO keywords (project_id, phrase) VALUES (:project_id, :phrase)');
        $stmt->execute(['project_id' => $projectId, 'phrase' => $phrase]);
        return (int) $this->pdo->lastInsertId();
    }

    protected function seedPosition(int $keywordId, int $position, string $date): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO positions (keyword_id, position, captured_at) VALUES (:keyword_id, :position, :captured_at)'
        );
        $stmt->execute(['keyword_id' => $keywordId, 'position' => $position, 'captured_at' => $date]);
    }
}
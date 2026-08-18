<?php

declare(strict_types=1);

namespace App\Tests\Support;

use App\Core\Request;
use App\Core\Response;
use App\Core\Router;
use App\Models\Database;
use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class HttpTestCase extends BaseTestCase
{
    protected \PDO $pdo;
    private ?string $dbFile = null;

    protected function setUp(): void
    {
        $this->dbFile = sys_get_temp_dir() . '/minirank_http_' . bin2hex(random_bytes(6)) . '.db';
        putenv('DATABASE_PATH=' . $this->dbFile);
        $this->resetDatabase();
        $this->pdo = Database::connection();
        $this->pdo->exec(file_get_contents(dirname(__DIR__, 2) . '/database/schema.sql'));

        $_SESSION = [];
        $_GET = [];
        $_POST = [];
        $_SERVER['REQUEST_METHOD'] = 'GET';
        http_response_code(200);

        Response::$abort = static function (): void {
            throw new RedirectSignal('redirect');
        };
    }

    protected function tearDown(): void
    {
        Response::$abort = null;
        $_SESSION = [];
        $_GET = [];
        $_POST = [];
        http_response_code(200);
        unset($this->pdo);
        if ($this->dbFile !== null && is_file($this->dbFile)) {
            unlink($this->dbFile);
        }
    }

    private function resetDatabase(): void
    {
        $ref = new \ReflectionClass(Database::class);
        $prop = $ref->getProperty('instance');
        $prop->setValue(null, null);
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

    protected function setUpAuth(int $userId): void
    {
        $_SESSION['user_id'] = $userId;
    }

    protected function setMethod(string $method): void
    {
        $_SERVER['REQUEST_METHOD'] = $method;
    }

    protected function runRoute(string $route, array $query = []): void
    {
        $_GET = ['route' => $route] + $query;
        (new Router())->dispatch(new Request(), $route);
    }
}
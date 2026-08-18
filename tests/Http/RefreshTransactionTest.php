<?php

declare(strict_types=1);

namespace App\Tests\Http;

use App\Controllers\PositionController;
use App\Core\Request;
use App\Models\Keyword;
use App\Models\Position;
use App\Models\Project;
use App\Tests\Support\HttpTestCase;

class RefreshTransactionTest extends HttpTestCase
{
    public function testRefreshFailsAtomicallyOnMidBatchFailure(): void
    {
        $user = $this->seedUser();
        $project = $this->seedProject($user, 'p.example');
        $this->seedKeyword($project, 'kw one');
        $this->seedKeyword($project, 'kw two');

        $this->setUpAuth($user);
        $_SESSION['csrf'] = 'valid-token';
        $_POST = ['csrf_token' => 'valid-token', 'project' => $project];
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $failing = new class ($this->pdo) extends Position {
            public function refreshForToday(int $keywordId): array
            {
                throw new \PDOException('simulated failure', 23000);
            }
        };

        $controller = new PositionController(new Keyword($this->pdo), $failing, new Project($this->pdo));

        try {
            $controller->refresh(new Request());
            $this->fail('Expected a PDOException from the failing refresh.');
        } catch (\PDOException $e) {
            $this->assertSame('simulated failure', $e->getMessage());
        } catch (\Throwable $e) {
            $this->fail('Expected PDOException, got ' . get_class($e) . ': ' . $e->getMessage());
        }

        $stmt = $this->pdo->query('SELECT COUNT(*) FROM positions');
        $this->assertSame(0, (int) $stmt->fetchColumn());
    }
}
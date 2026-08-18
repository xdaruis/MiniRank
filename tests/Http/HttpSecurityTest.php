<?php

declare(strict_types=1);

namespace App\Tests\Http;

use App\Tests\Support\HttpTestCase;
use App\Tests\Support\RedirectSignal;

class HttpSecurityTest extends HttpTestCase
{
    public function testCrossUserDetailReturns404(): void
    {
        $userA = $this->seedUser('alice');
        $projectA = $this->seedProject($userA, 'a.example');
        $keywordA = $this->seedKeyword($projectA, 'alice keyword');

        $userB = $this->seedUser('bob');
        $this->seedProject($userB, 'b.example');

        $this->setUpAuth($userB);
        $this->setMethod('GET');

        ob_start();
        $this->runRoute('keyword.detail', ['id' => $keywordA, 'project' => $projectA]);
        $output = ob_get_clean();
        $this->assertNotSame('', $output);
        $this->assertSame(404, http_response_code());
    }

    public function testOwnedDetailReturnsOk(): void
    {
        $userA = $this->seedUser('alice');
        $projectA = $this->seedProject($userA, 'a.example');
        $keywordA = $this->seedKeyword($projectA, 'alice keyword');

        $this->setUpAuth($userA);
        $this->setMethod('GET');

        ob_start();
        $this->runRoute('keyword.detail', ['id' => $keywordA, 'project' => $projectA]);
        ob_get_clean();
        $this->assertSame(200, http_response_code());
    }

    public function testRefreshWithoutCsrfReturns403(): void
    {
        $user = $this->seedUser();
        $project = $this->seedProject($user, 'p.example');
        $this->seedKeyword($project, 'kw');

        $this->setUpAuth($user);
        $this->setMethod('POST');
        $_POST = [];

        ob_start();
        $this->runRoute('position.refresh', ['project' => $project]);
        $output = ob_get_clean();

        $this->assertSame(403, http_response_code());
        $this->assertSame('Invalid CSRF token', json_decode($output, true)['error'] ?? null);
    }

    public function testRefreshCrossProjectReturns404(): void
    {
        $userB = $this->seedUser('bob');
        $this->seedProject($userB, 'b.example');

        $userA = $this->seedUser('alice');
        $projectA = $this->seedProject($userA, 'a.example');

        $this->setUpAuth($userB);
        $this->setMethod('POST');
        $_SESSION['csrf'] = 'valid-token';
        $_POST = ['csrf_token' => 'valid-token', 'project' => $projectA];

        ob_start();
        $this->runRoute('position.refresh', ['project' => $projectA]);
        $output = ob_get_clean();

        $this->assertSame(404, http_response_code());
        $this->assertSame('Project not found', json_decode($output, true)['error'] ?? null);
    }

    public function testGetDeleteDoesNotDelete(): void
    {
        $userA = $this->seedUser('alice');
        $projectA = $this->seedProject($userA, 'a.example');
        $keywordA = $this->seedKeyword($projectA, 'alice keyword');

        $this->setUpAuth($userA);
        $this->setMethod('GET');
        $_SESSION['csrf'] = 'valid-token';
        $_POST = ['csrf_token' => 'valid-token', 'id' => $keywordA];

        try {
            $this->runRoute('keyword.delete', ['project' => $projectA]);
        } catch (RedirectSignal) {
        }

        $this->assertTrue($this->keywordExists($keywordA));
    }

    public function testPostDeleteOwnedRemovesKeyword(): void
    {
        $userA = $this->seedUser('alice');
        $projectA = $this->seedProject($userA, 'a.example');
        $keywordA = $this->seedKeyword($projectA, 'alice keyword');

        $this->setUpAuth($userA);
        $this->setMethod('POST');
        $_SESSION['csrf'] = 'valid-token';
        $_POST = ['csrf_token' => 'valid-token', 'id' => $keywordA];

        try {
            $this->runRoute('keyword.delete', ['project' => $projectA]);
        } catch (RedirectSignal) {
        }

        $this->assertFalse($this->keywordExists($keywordA));
    }

    public function testPostDeleteForeignKeywordDoesNotDelete(): void
    {
        $userA = $this->seedUser('alice');
        $projectA = $this->seedProject($userA, 'a.example');
        $keywordA = $this->seedKeyword($projectA, 'alice keyword');

        $userB = $this->seedUser('bob');
        $this->seedProject($userB, 'b.example');

        $this->setUpAuth($userB);
        $this->setMethod('POST');
        $_SESSION['csrf'] = 'valid-token';
        $_POST = ['csrf_token' => 'valid-token', 'id' => $keywordA];

        try {
            $this->runRoute('keyword.delete', ['project' => $projectA]);
        } catch (RedirectSignal) {
        }

        $this->assertTrue($this->keywordExists($keywordA));
    }

    private function keywordExists(int $id): bool
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM keywords WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return (int) $stmt->fetchColumn() === 1;
    }
}
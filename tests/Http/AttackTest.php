<?php

declare(strict_types=1);

namespace App\Tests\Http;

use App\Tests\Support\HttpTestCase;
use App\Tests\Support\RedirectSignal;
use PHPUnit\Framework\Attributes\DataProvider;

class AttackTest extends HttpTestCase
{
    private function setUpProjectAndAuth(): int
    {
        $userId = $this->seedUser('alice');
        $this->seedProject($userId, 'a.example');
        $this->setUpAuth($userId);
        $stmt = $this->pdo->prepare('SELECT id FROM projects WHERE user_id = :uid ORDER BY id LIMIT 1');
        $stmt->execute(['uid' => $userId]);
        return (int) $stmt->fetchColumn();
    }

    private function captureRoute(string $route, array $query = []): string
    {
        ob_start();
        $this->runRoute($route, $query);
        return (string) ob_get_clean();
    }

    public function testSearchInjectionDoesNotLeakOrBreak(): void
    {
        $project = $this->setUpProjectAndAuth();
        $this->seedKeyword($project, 'alpha target');
        $this->seedKeyword($project, 'beta target');

        $output = $this->captureRoute('keyword.list', [
            'project' => $project,
            'q' => "x' OR '1'='1' -- ",
        ]);

        $this->assertSame(200, http_response_code());
        $this->assertStringNotContainsString('alpha target', $output);
        $this->assertStringNotContainsString('beta target', $output);

        $this->seedUser('leakmarker');
        $output = $this->captureRoute('keyword.list', [
            'project' => $project,
            'q' => "' UNION SELECT id, username, password_hash FROM users--",
        ]);

        $this->assertSame(200, http_response_code());
        $this->assertStringNotContainsString('leakmarker', $output);
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM users');
        $stmt->execute();
        $this->assertSame(2, (int) $stmt->fetchColumn());
    }

    public function testSearchDropWasNotExecuted(): void
    {
        $project = $this->setUpProjectAndAuth();
        $this->seedKeyword($project, 'survivor');

        $this->captureRoute('keyword.list', [
            'project' => $project,
            'q' => "1; DROP TABLE keywords;--",
        ]);

        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM keywords WHERE project_id = :project');
        $stmt->execute(['project' => $project]);
        $this->assertSame(1, (int) $stmt->fetchColumn());
    }

    public function testAddInjectionStoredAsLiteral(): void
    {
        $project = $this->setUpProjectAndAuth();
        $payload = "x'); DROP TABLE positions;--";

        $this->setMethod('POST');
        $_SESSION['csrf'] = 'valid-token';
        $_POST = ['csrf_token' => 'valid-token', 'phrase' => $payload];

        try {
            $this->runRoute('keyword.add', ['project' => $project]);
        } catch (RedirectSignal) {
        }

        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM sqlite_master WHERE type = :type AND name = :name');
        $stmt->execute(['type' => 'table', 'name' => 'positions']);
        $this->assertSame(1, (int) $stmt->fetchColumn());

        $stmt = $this->pdo->prepare('SELECT phrase FROM keywords WHERE project_id = :project');
        $stmt->execute(['project' => $project]);
        $this->assertSame($payload, (string) $stmt->fetchColumn());
    }

    public function testEditInjectionStoredAsLiteral(): void
    {
        $project = $this->setUpProjectAndAuth();
        $keywordId = $this->seedKeyword($project, 'original');
        $payload = "1; DROP TABLE positions; --";

        $this->setMethod('POST');
        $_SESSION['csrf'] = 'valid-token';
        $_POST = ['csrf_token' => 'valid-token', 'phrase' => $payload];

        try {
            $this->runRoute('keyword.edit', ['id' => $keywordId, 'project' => $project]);
        } catch (RedirectSignal) {
        }

        $stmt = $this->pdo->prepare('SELECT phrase FROM keywords WHERE id = :id');
        $stmt->execute(['id' => $keywordId]);
        $this->assertSame($payload, (string) $stmt->fetchColumn());
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function xssProvider(): iterable
    {
        yield 'script' => ['<script>alert(1)</script>'];
        yield 'img onerror' => ['<img src=x onerror=alert(1)>'];
        yield 'svg onload' => ['"><svg onload=alert(1)>'];
        yield 'template' => ["{{constructor.constructor('alert(1)')()}}"];
        yield 'encoded angle' => ['%3cscript%3ealert(1)%3c/script%3e'];
    }

    #[DataProvider('xssProvider')]
    public function testSearchXssReflectedEscaped(string $payload): void
    {
        $project = $this->setUpProjectAndAuth();

        $output = $this->captureRoute('keyword.list', ['project' => $project, 'q' => $payload]);

        $this->assertSame(200, http_response_code());
        $escaped = htmlspecialchars($payload, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $this->assertStringContainsString($escaped, $output);
        $this->assertStringNotContainsString('<script>alert(1)', $output);
        $this->assertStringNotContainsString('><img src=x onerror', $output);
        $this->assertStringNotContainsString('><svg onload', $output);
    }

    #[DataProvider('xssProvider')]
    public function testKeywordPhraseEscapedInList(string $payload): void
    {
        $project = $this->setUpProjectAndAuth();
        $this->seedKeyword($project, $payload);

        $output = $this->captureRoute('keyword.list', ['project' => $project]);

        $escaped = htmlspecialchars($payload, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $this->assertStringContainsString($escaped, $output);
        $this->assertStringNotContainsString('<script>alert(1)', $output);
        $this->assertStringNotContainsString('<img src=x onerror', $output);
        $this->assertStringNotContainsString('<svg onload', $output);
    }

    #[DataProvider('xssProvider')]
    public function testKeywordPhraseEscapedInDetail(string $payload): void
    {
        $project = $this->setUpProjectAndAuth();
        $keywordId = $this->seedKeyword($project, $payload);

        $output = $this->captureRoute('keyword.detail', ['id' => $keywordId, 'project' => $project]);

        $escaped = htmlspecialchars($payload, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $this->assertStringContainsString($escaped, $output);
        $this->assertStringNotContainsString('<script>alert(1)', $output);
    }

    #[DataProvider('xssProvider')]
    public function testKeywordPhraseEscapedInEditForm(string $payload): void
    {
        $project = $this->setUpProjectAndAuth();
        $keywordId = $this->seedKeyword($project, $payload);

        $output = $this->captureRoute('keyword.edit', ['id' => $keywordId, 'project' => $project]);

        $escaped = htmlspecialchars($payload, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $this->assertStringContainsString($escaped, $output);
        $this->assertStringNotContainsString('<script>alert(1)', $output);
    }

    #[DataProvider('xssProvider')]
    public function testAddXssPhrasePersistsEscaped(string $payload): void
    {
        $project = $this->setUpProjectAndAuth();

        $this->setMethod('POST');
        $_SESSION['csrf'] = 'valid-token';
        $_POST = ['csrf_token' => 'valid-token', 'phrase' => $payload];

        try {
            $this->runRoute('keyword.add', ['project' => $project]);
        } catch (RedirectSignal) {
        }

        $stmt = $this->pdo->prepare('SELECT phrase FROM keywords WHERE project_id = :project');
        $stmt->execute(['project' => $project]);
        $this->assertSame($payload, (string) $stmt->fetchColumn());

        $output = $this->captureRoute('keyword.list', ['project' => $project]);

        $escaped = htmlspecialchars($payload, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $this->assertStringContainsString($escaped, $output);
        $this->assertStringNotContainsString('<script>alert(1)', $output);
    }
}
<?php

declare(strict_types=1);

namespace App\Tests\Http;

use App\Tests\Support\HttpTestCase;

class PaginationTest extends HttpTestCase
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

    public function testListSlicesAcrossPages(): void
    {
        $project = $this->setUpProjectAndAuth();
        $phrases = [];
        for ($i = 1; $i <= 25; $i++) {
            $phrases[] = 'keyword ' . $i;
            $this->seedKeyword($project, 'keyword ' . $i);
        }

        $page1 = $this->captureRoute('keyword.list', ['project' => $project, 'per_page' => 20, 'page' => 1]);
        $this->assertStringContainsString('keyword 1', $page1);
        $this->assertStringNotContainsString('keyword 21', $page1);

        $page2 = $this->captureRoute('keyword.list', ['project' => $project, 'per_page' => 20, 'page' => 2]);
        $this->assertStringContainsString('keyword 21', $page2);
        $this->assertStringNotContainsString('keyword 1', $page2);
        $this->assertStringContainsString('Page 2 of 2', $page2);
    }

    public function testListHeaderShowsTotalNotPageCount(): void
    {
        $project = $this->setUpProjectAndAuth();
        for ($i = 1; $i <= 25; $i++) {
            $this->seedKeyword($project, 'keyword ' . $i);
        }

        $output = $this->captureRoute('keyword.list', ['project' => $project, 'per_page' => 10, 'page' => 2]);

        $this->assertStringContainsString('>25</strong> keywords', $output);
        $this->assertStringContainsString('Page 2 of 3', $output);
    }

    public function testListPageClampedToLast(): void
    {
        $project = $this->setUpProjectAndAuth();
        for ($i = 1; $i <= 25; $i++) {
            $this->seedKeyword($project, 'keyword ' . $i);
        }

        $output = $this->captureRoute('keyword.list', ['project' => $project, 'per_page' => 10, 'page' => 99]);

        $this->assertStringContainsString('keyword 21', $output);
        $this->assertStringNotContainsString('keyword 1', $output);
        $this->assertStringContainsString('Page 3 of 3', $output);
    }

    public function testListNavPreservesFilters(): void
    {
        $project = $this->setUpProjectAndAuth();
        for ($i = 1; $i <= 30; $i++) {
            $keywordId = $this->seedKeyword($project, 'pizza ' . $i);
            $this->seedPosition($keywordId, 5, date('Y-m-d'));
            $this->seedPosition($keywordId, 9, date('Y-m-d', strtotime('-7 days')));
        }

        $output = $this->captureRoute('keyword.list', [
            'project' => $project,
            'per_page' => 10,
            'q' => 'pizza',
            'move' => 'improved',
            'pos_min' => 1,
            'pos_max' => 100,
        ]);

        $this->assertStringContainsString('q=pizza', $output);
        $this->assertStringContainsString('move=improved', $output);
        $this->assertStringContainsString('pos_min=1', $output);
        $this->assertStringContainsString('pos_max=100', $output);
        $this->assertStringContainsString('page=2', $output);
    }

    public function testSearchWithPaginationSlices(): void
    {
        $project = $this->setUpProjectAndAuth();
        for ($i = 1; $i <= 30; $i++) {
            $this->seedKeyword($project, 'pizza ' . $i);
            $this->seedKeyword($project, 'pasta ' . $i);
        }

        $page1 = $this->captureRoute('keyword.list', ['project' => $project, 'per_page' => 10, 'q' => 'pizza']);
        $page2 = $this->captureRoute('keyword.list', ['project' => $project, 'per_page' => 10, 'q' => 'pizza', 'page' => 2]);

        $this->assertStringContainsString('pizza 1', $page1);
        $this->assertStringNotContainsString('pasta 1', $page1);
        $this->assertStringContainsString('pizza 11', $page2);
        $this->assertStringNotContainsString('pasta 11', $page2);
        $this->assertStringContainsString('Page 2 of 3', $page2);
    }

    public function testHistoryTablePaginatesButChartKeepsFullSet(): void
    {
        $project = $this->setUpProjectAndAuth();
        $keywordId = $this->seedKeyword($project, 'history kw');
        for ($i = 0; $i < 30; $i++) {
            $this->seedPosition($keywordId, 10 + $i, date('Y-m-d', strtotime("-$i days")));
        }

        $output = $this->captureRoute('keyword.detail', ['id' => $keywordId, 'project' => $project, 'per_page' => 10, 'page' => 1]);

        $this->assertStringContainsString('30 days of history', $output);
        $this->assertStringContainsString('<svg', $output);
        $this->assertStringContainsString('Page 1 of 3', $output);
    }

    public function testHistoryLastPageClamped(): void
    {
        $project = $this->setUpProjectAndAuth();
        $keywordId = $this->seedKeyword($project, 'history kw');
        for ($i = 0; $i < 30; $i++) {
            $this->seedPosition($keywordId, 10 + $i, date('Y-m-d', strtotime("-$i days")));
        }

        $output = $this->captureRoute('keyword.detail', ['id' => $keywordId, 'project' => $project, 'per_page' => 10, 'page' => 9]);

        $this->assertStringContainsString('Page 3 of 3', $output);
    }

    public function testNavHasFirstLastButtons(): void
    {
        $project = $this->setUpProjectAndAuth();
        for ($i = 1; $i <= 25; $i++) {
            $this->seedKeyword($project, 'keyword ' . $i);
        }

        $output = $this->captureRoute('keyword.list', ['project' => $project, 'per_page' => 10, 'page' => 2]);

        $this->assertStringContainsString('First', $output);
        $this->assertStringContainsString('Last', $output);
        $this->assertStringContainsString('page=1', $output);
        $this->assertStringContainsString('page=3', $output);
    }

    public function testNavJumpFormPreservesParams(): void
    {
        $project = $this->setUpProjectAndAuth();
        for ($i = 1; $i <= 25; $i++) {
            $keywordId = $this->seedKeyword($project, 'pizza ' . $i);
            $this->seedPosition($keywordId, 50, date('Y-m-d'));
        }

        $output = $this->captureRoute('keyword.list', [
            'project' => $project,
            'per_page' => 10,
            'q' => 'pizza',
            'pos_min' => 1,
            'pos_max' => 100,
        ]);

        $this->assertStringContainsString('type="number" name="page"', $output);
        $this->assertStringContainsString('name="project"', $output);
        $this->assertStringContainsString('name="q"', $output);
        $this->assertStringContainsString('name="pos_min"', $output);
    }

    public function testSinglePageRendersNoNav(): void
    {
        $project = $this->setUpProjectAndAuth();
        $this->seedKeyword($project, 'one');

        $output = $this->captureRoute('keyword.list', ['project' => $project]);

        $this->assertStringNotContainsString('Page 1 of', $output);
        $this->assertStringNotContainsString('class="page-item', $output);
    }
}
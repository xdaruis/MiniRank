<?php

declare(strict_types=1);

namespace App\Tests\Models;

use App\Tests\Support\TestCase;
use App\Models\Keyword;
use App\Models\Position;

class PositionTest extends TestCase
{
    private int $projectId;
    private int $keywordId;

    protected function setUp(): void
    {
        parent::setUp();
        $userId = $this->seedUser();
        $this->projectId = $this->seedProject($userId, 'p.example');
        $this->keywordId = $this->seedKeyword($this->projectId, 'test phrase');
    }

    public function testTrendImprovedWhenRankFalls(): void
    {
        $this->seedPosition($this->keywordId, 5, date('Y-m-d'));
        $this->seedPosition($this->keywordId, 9, date('Y-m-d', strtotime('-7 days')));
        $this->assertSame('improved', (new Position($this->pdo))->trend($this->keywordId));
    }

    public function testTrendUsesNearestPriorRowWhenSevenDaysMissing(): void
    {
        $this->seedPosition($this->keywordId, 5, date('Y-m-d'));
        $this->seedPosition($this->keywordId, 9, date('Y-m-d', strtotime('-14 days')));
        $this->assertSame('improved', (new Position($this->pdo))->trend($this->keywordId));
    }

    public function testTrendFallsBackToOldestWhenYoungerThanWindow(): void
    {
        $this->seedPosition($this->keywordId, 5, date('Y-m-d'));
        $this->seedPosition($this->keywordId, 9, date('Y-m-d', strtotime('-2 days')));
        $this->assertSame('improved', (new Position($this->pdo))->trend($this->keywordId));
    }

    public function testTrendDeclinedWhenRankRises(): void
    {
        $this->seedPosition($this->keywordId, 12, date('Y-m-d'));
        $this->seedPosition($this->keywordId, 8, date('Y-m-d', strtotime('-7 days')));
        $this->assertSame('declined', (new Position($this->pdo))->trend($this->keywordId));
    }

    public function testTrendStableWhenEqual(): void
    {
        $this->seedPosition($this->keywordId, 10, date('Y-m-d'));
        $this->seedPosition($this->keywordId, 10, date('Y-m-d', strtotime('-7 days')));
        $this->assertSame('stable', (new Position($this->pdo))->trend($this->keywordId));
    }

    public function testTrendStableWithSingleRowFallback(): void
    {
        $this->seedPosition($this->keywordId, 20, date('Y-m-d'));
        $this->assertSame('stable', (new Position($this->pdo))->trend($this->keywordId));
    }

    public function testRefreshClampsPositionAtLowerBound(): void
    {
        $this->seedPosition($this->keywordId, 1, date('Y-m-d'));
        $result = (new Position($this->pdo))->refreshForToday($this->keywordId);
        $this->assertGreaterThanOrEqual(1, $result['position']);
        $this->assertLessThanOrEqual(100, $result['position']);
    }

    public function testRefreshClampsPositionAtUpperBound(): void
    {
        $this->seedPosition($this->keywordId, 100, date('Y-m-d'));
        $result = (new Position($this->pdo))->refreshForToday($this->keywordId);
        $this->assertGreaterThanOrEqual(1, $result['position']);
        $this->assertLessThanOrEqual(100, $result['position']);
    }

    public function testRefreshUpsertsSameDay(): void
    {
        $position = new Position($this->pdo);
        $position->refreshForToday($this->keywordId);
        $position->refreshForToday($this->keywordId);

        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM positions WHERE keyword_id = :id');
        $stmt->execute(['id' => $this->keywordId]);
        $this->assertSame(1, (int) $stmt->fetchColumn());
    }

    public function testRefreshReturnsTrendKey(): void
    {
        $result = (new Position($this->pdo))->refreshForToday($this->keywordId);
        $this->assertArrayHasKey('keyword_id', $result);
        $this->assertArrayHasKey('position', $result);
        $this->assertArrayHasKey('trend', $result);
        $this->assertContains($result['trend'], ['improved', 'declined', 'stable']);
    }

    public function testKeywordCreateProducesRefreshableKeyword(): void
    {
        $id = (new Keyword($this->pdo))->create($this->projectId, 'another phrase');
        $result = (new Position($this->pdo))->refreshForToday($id);
        $this->assertSame($id, $result['keyword_id']);
    }
}
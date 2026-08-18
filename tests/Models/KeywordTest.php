<?php

declare(strict_types=1);

namespace App\Tests\Models;

use App\Models\Keyword;
use App\Tests\Support\TestCase;

class KeywordTest extends TestCase
{
    private int $projectId;
    private int $keywordId;

    protected function setUp(): void
    {
        parent::setUp();
        $userId = $this->seedUser();
        $this->projectId = $this->seedProject($userId, 'p.example');
        $this->keywordId = $this->seedKeyword($this->projectId, 'original phrase');
    }

    public function testUpdateChangesPhrase(): void
    {
        $keyword = new Keyword($this->pdo);
        $keyword->update($this->keywordId, 'renamed phrase');

        $this->assertSame('renamed phrase', $keyword->findOwned($this->projectOwner(), $this->keywordId)['phrase'] ?? null);
    }

    public function testDeleteRemovesKeyword(): void
    {
        $keyword = new Keyword($this->pdo);
        $keyword->delete($this->keywordId);

        $this->assertNull($keyword->findOwned($this->projectOwner(), $this->keywordId));
    }

    public function testAllFiltersBySearchTerm(): void
    {
        $this->seedKeyword($this->projectId, 'best pizza berlin');
        $userId = $this->projectOwner();

        $keyword = new Keyword($this->pdo);
        $rows = $keyword->all($userId, $this->projectId, 'pizza');
        $phrases = array_column($rows, 'phrase');

        $this->assertContains('best pizza berlin', $phrases);
        $this->assertNotContains('original phrase', $phrases);

        $all = $keyword->all($userId, $this->projectId, '');
        $this->assertCount(2, $all);
    }

    public function testAllSearchIsCaseInsensitive(): void
    {
        $this->seedKeyword($this->projectId, 'PIZZA Berlin');
        $keyword = new Keyword($this->pdo);

        $rows = $keyword->all($this->projectOwner(), $this->projectId, 'pizza');
        $this->assertNotEmpty($rows);
    }

    public function testAllReturnsCurrentPositionSubquery(): void
    {
        $this->seedPosition($this->keywordId, 42, date('Y-m-d'));
        $keyword = new Keyword($this->pdo);

        $rows = $keyword->all($this->projectOwner(), $this->projectId);
        $this->assertSame(42, (int) $rows[0]['position']);
    }

    private function projectOwner(): int
    {
        $stmt = $this->pdo->prepare('SELECT user_id FROM projects WHERE id = :id');
        $stmt->execute(['id' => $this->projectId]);
        return (int) $stmt->fetchColumn();
    }
}
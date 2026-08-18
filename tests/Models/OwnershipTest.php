<?php

declare(strict_types=1);

namespace App\Tests\Models;

use App\Tests\Support\TestCase;
use App\Models\Keyword;
use App\Models\Project;

class OwnershipTest extends TestCase
{
    private int $userA;
    private int $userB;
    private int $projectA;
    private int $projectB;
    private int $keywordOwned;
    private int $keywordForeign;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userA = $this->seedUser('alice');
        $this->userB = $this->seedUser('bob');
        $this->projectA = $this->seedProject($this->userA, 'a.example');
        $this->projectB = $this->seedProject($this->userB, 'b.example');
        $this->keywordOwned = $this->seedKeyword($this->projectA, 'alice keyword');
        $this->keywordForeign = $this->seedKeyword($this->projectB, 'bob keyword');
    }

    public function testFindOwnedReturnsNullForForeignUser(): void
    {
        $keyword = new Keyword($this->pdo);
        $this->assertNull($keyword->findOwned($this->userB, $this->keywordOwned));
        $this->assertNull($keyword->findOwned($this->userA, $this->keywordForeign));
    }

    public function testFindOwnedReturnsRowForOwner(): void
    {
        $keyword = new Keyword($this->pdo);
        $row = $keyword->findOwned($this->userA, $this->keywordOwned);
        $this->assertNotNull($row);
        $this->assertSame('alice keyword', $row['phrase']);
    }

    public function testProjectOwnsIsolatedByUser(): void
    {
        $project = new Project($this->pdo);
        $this->assertTrue($project->owns($this->userA, $this->projectA));
        $this->assertFalse($project->owns($this->userB, $this->projectA));
        $this->assertFalse($project->owns($this->userA, $this->projectB));
    }

    public function testAllReturnsOnlyOwnScopedKeywords(): void
    {
        $keyword = new Keyword($this->pdo);
        $rows = $keyword->all($this->userA, $this->projectA);
        $phrases = array_column($rows, 'phrase');
        $this->assertContains('alice keyword', $phrases);
        $this->assertNotContains('bob keyword', $phrases);
    }

    public function testIdsForUserExcludesForeignKeywords(): void
    {
        $keyword = new Keyword($this->pdo);
        $ids = array_column($keyword->idsForUser($this->userA, $this->projectA), 'id');
        $this->assertContains($this->keywordOwned, $ids);
        $this->assertNotContains($this->keywordForeign, $ids);
    }

    public function testDuplicatePhraseInProjectThrowsIntegrityError(): void
    {
        $keyword = new Keyword($this->pdo);
        $keyword->create($this->projectA, 'fresh duplicate phrase');
        $this->expectException(\PDOException::class);
        $this->expectExceptionCode('23000');
        $keyword->create($this->projectA, 'fresh duplicate phrase');
    }

    public function testSamePhraseAllowedAcrossProjects(): void
    {
        $keyword = new Keyword($this->pdo);
        $id = $keyword->create($this->projectB, 'alice keyword');
        $this->assertNotSame(0, $id);
    }
}
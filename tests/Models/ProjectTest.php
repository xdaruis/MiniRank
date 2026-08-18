<?php

declare(strict_types=1);

namespace App\Tests\Models;

use App\Models\Project;
use App\Tests\Support\TestCase;

class ProjectTest extends TestCase
{
    private int $userA;
    private int $userB;
    private int $projectA1;
    private int $projectA2;
    private int $projectB1;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userA = $this->seedUser('alice');
        $this->userB = $this->seedUser('bob');
        $this->projectA1 = $this->seedProject($this->userA, 'a1.example');
        $this->projectA2 = $this->seedProject($this->userA, 'a2.example');
        $this->projectB1 = $this->seedProject($this->userB, 'b1.example');
    }

    public function testUserProjectsReturnsOnlyOwned(): void
    {
        $project = new Project($this->pdo);
        $ids = array_column($project->userProjects($this->userA), 'id');
        $this->assertContains($this->projectA1, $ids);
        $this->assertContains($this->projectA2, $ids);
        $this->assertNotContains($this->projectB1, $ids);
    }

    public function testUserProjectsEmptyWhenNone(): void
    {
        $userC = $this->seedUser('carol');
        $project = new Project($this->pdo);
        $this->assertSame([], $project->userProjects($userC));
    }

    public function testFirstForReturnsLowestOwnedOrNull(): void
    {
        $project = new Project($this->pdo);
        $this->assertSame($this->projectA1, (int) $project->firstFor($this->userA)['id']);
        $this->assertSame($this->projectB1, (int) $project->firstFor($this->userB)['id']);

        $userC = $this->seedUser('carol');
        $this->assertNull($project->firstFor($userC));
    }

    public function testCreateAddsOwnedProject(): void
    {
        $project = new Project($this->pdo);
        $id = $project->create($this->userA, 'new.example');
        $this->assertNotSame(0, $id);
        $this->assertTrue($project->owns($this->userA, $id));
        $this->assertFalse($project->owns($this->userB, $id));
    }
}
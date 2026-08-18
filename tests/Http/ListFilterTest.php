<?php

declare(strict_types=1);

namespace App\Tests\Http;

use App\Tests\Support\HttpTestCase;

class ListFilterTest extends HttpTestCase
{
    private function setUpProjectAndAuth(): array
    {
        $user = $this->seedUser('alice');
        $project = $this->seedProject($user, 'a.example');
        $this->setUpAuth($user);
        return [$user, $project];
    }

    public function testSearchFiltersKeywords(): void
    {
        [, $project] = $this->setUpProjectAndAuth();
        $this->seedKeyword($project, 'best pizza berlin');
        $this->seedKeyword($project, 'renovation service');

        ob_start();
        $this->runRoute('keyword.list', ['q' => 'pizza']);
        $output = ob_get_clean();

        $this->assertStringContainsString('best pizza berlin', $output);
        $this->assertStringNotContainsString('renovation service', $output);
    }

    public function testMoveFilterImproved(): void
    {
        [$user, $project] = $this->setUpProjectAndAuth();
        $improved = $this->seedKeyword($project, 'improved keyword');
        $declined = $this->seedKeyword($project, 'declined keyword');
        $this->seedPosition($improved, 5, date('Y-m-d'));
        $this->seedPosition($improved, 9, date('Y-m-d', strtotime('-7 days')));
        $this->seedPosition($declined, 12, date('Y-m-d'));
        $this->seedPosition($declined, 8, date('Y-m-d', strtotime('-7 days')));
        $this->assertNotSame(0, $user);

        ob_start();
        $this->runRoute('keyword.list', ['move' => 'improved']);
        $output = ob_get_clean();

        $this->assertStringContainsString('improved keyword', $output);
        $this->assertStringNotContainsString('declined keyword', $output);
    }

    public function testPositionRangeFilter(): void
    {
        [, $project] = $this->setUpProjectAndAuth();
        $low = $this->seedKeyword($project, 'top ranked phrase');
        $high = $this->seedKeyword($project, 'deep ranked phrase');
        $this->seedPosition($low, 5, date('Y-m-d'));
        $this->seedPosition($high, 90, date('Y-m-d'));

        ob_start();
        $this->runRoute('keyword.list', ['pos_min' => 80, 'pos_max' => 100]);
        $output = ob_get_clean();

        $this->assertStringContainsString('deep ranked phrase', $output);
        $this->assertStringNotContainsString('top ranked phrase', $output);
    }

    public function testListFallsBackToFirstOwnedProject(): void
    {
        $user = $this->seedUser('alice');
        $firstProject = $this->seedProject($user, 'first.example');
        $secondProject = $this->seedProject($user, 'second.example');
        $this->seedKeyword($firstProject, 'kw first project');
        $this->seedKeyword($secondProject, 'kw second project');
        $this->setUpAuth($user);

        ob_start();
        $this->runRoute('keyword.list');
        $output = ob_get_clean();

        $this->assertStringContainsString('kw first project', $output);
        $this->assertStringNotContainsString('kw second project', $output);
    }

    public function testListWithoutProjectShowsAddPrompt(): void
    {
        $user = $this->seedUser('alice');
        $this->setUpAuth($user);

        ob_start();
        $this->runRoute('keyword.list');
        $output = ob_get_clean();

        $this->assertStringContainsString('No project yet', $output);
    }
}
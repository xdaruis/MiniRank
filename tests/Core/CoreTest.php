<?php

declare(strict_types=1);

namespace App\Tests\Core;

use App\Tests\Support\TestCase;
use App\Core\Csrf;
use App\Core\Response;

class CoreTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION = [];
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
    }

    public function testCsrfTokenMintsOncePerSession(): void
    {
        $first = Csrf::token();
        $second = Csrf::token();
        $this->assertSame(64, strlen($first));
        $this->assertSame($first, $second);
    }

    public function testCsrfVerifyPositive(): void
    {
        $token = Csrf::token();
        $this->assertTrue(Csrf::verify($token));
    }

    public function testCsrfVerifyNegative(): void
    {
        Csrf::token();
        $this->assertFalse(Csrf::verify('wrong-token'));
        $this->assertFalse(Csrf::verify(null));
        $this->assertFalse(Csrf::verify(''));
    }

    public function testResponseEscapesHtml(): void
    {
        $this->assertSame('&lt;&quot;&amp;&gt;', Response::e('<"&>'));
        $this->assertSame('', Response::e(null));
    }
}
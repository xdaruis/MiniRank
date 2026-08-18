<?php

declare(strict_types=1);

namespace App\Tests\Support;

class RedirectSignal extends \Exception
{
    public function __construct(public string $url)
    {
        parent::__construct('Redirect: ' . $url);
    }
}
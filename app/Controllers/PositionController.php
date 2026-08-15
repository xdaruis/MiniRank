<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Models\Database;
use App\Models\Keyword;

class PositionController
{
    private Keyword $keyword;

    public function __construct(?Keyword $keyword = null)
    {
        $this->keyword = $keyword ?? new Keyword(Database::connection());
    }

    public function refresh(Request $request): void
    {
        if (!$request->isPost()) {
            Response::json(['error' => 'Method not allowed'], 405);
            return;
        }

        // TODO: for each keyword, call Position::refreshForToday() and collect results.
        $results = [];

        Response::json($results);
    }
}

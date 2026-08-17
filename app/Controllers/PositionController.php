<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Models\Database;
use App\Models\Keyword;
use App\Models\Position;

class PositionController
{
    private Keyword $keyword;
    private Position $position;

    public function __construct(?Keyword $keyword = null, ?Position $position = null)
    {
        $this->keyword = $keyword ?? new Keyword(Database::connection());
        $this->position = $position ?? new Position(Database::connection());
    }

    public function refresh(Request $request): void
    {
        if (!$request->isPost()) {
            Response::json(['error' => 'Method not allowed'], 405);
            return;
        }

        $results = [];
        foreach ($this->keyword->ids() as $keyword) {
            $results[] = $this->position->refreshForToday((int) $keyword['id']);
        }

        Response::json($results);
    }
}

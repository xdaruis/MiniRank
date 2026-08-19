<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Models\Database;
use App\Models\Keyword;
use App\Models\Position;
use App\Models\Project;

class PositionController
{
    private Keyword $keyword;
    private Position $position;
    private Project $project;

    public function __construct(?Keyword $keyword = null, ?Position $position = null, ?Project $project = null)
    {
        $this->keyword = $keyword ?? new Keyword(Database::connection());
        $this->position = $position ?? new Position(Database::connection());
        $this->project = $project ?? new Project(Database::connection());
    }

    public function refresh(Request $request): void
    {
        if (!$request->isPost()) {
            Response::json(['error' => 'Method not allowed'], 405);
            return;
        }

        if (!$request->validCsrf()) {
            Response::json(['error' => 'Invalid CSRF token'], 403);
            return;
        }

        $userId = (int) Auth::userId();
        $projectId = (int) $request->post('project', 0);

        if ($projectId <= 0 || !$this->project->owns($userId, $projectId)) {
            Response::json(['error' => 'Project not found'], 404);
            return;
        }

        $results = [];
        $db = Database::connection();
        $db->beginTransaction();
        try {
            foreach ($this->keyword->idsForUser($userId, $projectId) as $keyword) {
                $results[] = $this->position->refreshForToday((int) $keyword['id']);
            }
            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }

        Response::json($results);
    }
}
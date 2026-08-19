<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Models\Database;
use App\Models\Project;

class ProjectController
{
    private Project $project;

    public function __construct(?Project $project = null)
    {
        $this->project = $project ?? new Project(Database::connection());
    }

    public function add(Request $request): void
    {
        if ($request->isPost()) {
            if (!$request->validCsrf()) {
                Response::redirect('index.php?route=keyword.list');
                return;
            }

            $domain = trim((string) $request->post('domain'));

            if ($domain === '') {
                Response::view('layout', ['content' => fn () => Response::view('project/form', ['error' => 'Domain is required.'])]);
                return;
            }

            try {
                $projectId = $this->project->create((int) Auth::userId(), $domain);
                Response::redirect('index.php?route=keyword.list&project=' . $projectId);
            } catch (\PDOException $e) {
                if ($e->getCode() === '23000') {
                    Response::view('layout', ['content' => fn () => Response::view('project/form', ['error' => 'You already track this domain.'])]);
                    return;
                }
                throw $e;
            }
        }

        Response::view('layout', ['content' => fn () => Response::view('project/form', [])]);
    }
}
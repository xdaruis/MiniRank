<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Models\Database;
use App\Models\Keyword;
use App\Models\Position;
use App\Models\Project;

class KeywordController
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

    private function userId(): int
    {
        return (int) Auth::userId();
    }

    private function activeProject(int $requestedId): ?array
    {
        $userId = $this->userId();
        $projects = $this->project->userProjects($userId);

        if (empty($projects)) {
            return null;
        }

        if ($requestedId > 0 && $this->project->owns($userId, $requestedId)) {
            $project = null;
            foreach ($projects as $p) {
                if ($p['id'] === $requestedId) {
                    $project = $p;
                    break;
                }
            }
        } else {
            $project = $this->project->firstFor($userId) ?? $projects[0];
        }

        return ['project' => $project, 'projects' => $projects];
    }

    public function list(Request $request): void
    {
        $ctx = $this->activeProject((int) $request->query('project', 0));
        if ($ctx === null) {
            $this->renderNoProject();
            return;
        }

        $project = $ctx['project'];
        $search = (string) $request->query('q', '');
        $move = (string) $request->query('move', '');
        $min = $this->clamp((int) $request->query('pos_min', 0), 0, 100);
        $max = $this->clamp((int) $request->query('pos_max', 0), 0, 100);
        $allowed = ['', 'improved', 'declined', 'stable'];
        if (!in_array($move, $allowed, true)) {
            $move = '';
        }

        $keywords = $this->keyword->all($this->userId(), (int) $project['id'], $search);
        $move = $move !== '' ? $move : null;

        if ($min > 0 || $max > 0) {
            if ($min > $max && $max > 0) {
                [$min, $max] = [$max, $min];
            }
            $keywords = array_values(array_filter(
                $keywords,
                fn ($k) => $this->withinRange((int) ($k['position'] ?? 0), $min, $max)
            ));
        }

        if ($move !== null) {
            $keywords = array_values(array_filter(
                $keywords,
                fn ($k) => $this->position->trend((int) $k['id']) === $move
            ));
        }

        Response::view('layout', ['content' => fn () => Response::view('keyword/list', [
            'keywords' => $keywords,
            'search' => $search,
            'move' => (string) $request->query('move', ''),
            'pos_min' => (int) $request->query('pos_min', 0),
            'pos_max' => (int) $request->query('pos_max', 0),
            'projectId' => (int) $project['id'],
            'projectDomain' => (string) $project['domain'],
            'projects' => $ctx['projects'],
            'trend' => fn (int $id) => $this->position->trend($id),
        ])]);
    }

    private function clamp(int $value, int $low, int $high): int
    {
        return max($low, min($high, $value));
    }

    private function withinRange(int $position, int $min, int $max): bool
    {
        if ($min > 0 && $position < $min) {
            return false;
        }
        if ($max > 0 && $position > $max) {
            return false;
        }
        return true;
    }

    public function add(Request $request): void
    {
        $ctx = $this->activeProject((int) $request->query('project', 0));
        if ($ctx === null) {
            $this->renderNoProject();
            return;
        }
        $project = $ctx['project'];

        if ($request->isPost()) {
            if (!Csrf::verify((string) $request->post('csrf_token', ''))) {
                Response::redirect('index.php?route=keyword.list&project=' . $project['id']);
                return;
            }
            $phrase = trim((string) $request->post('phrase'));

            if ($phrase === '') {
                Response::view('layout', ['content' => fn () => Response::view('keyword/form', [
                    'keyword' => null,
                    'action' => 'keyword.add&project=' . $project['id'],
                    'projectId' => (int) $project['id'],
                    'error' => 'Phrase is required.',
                ])]);
                return;
            }

            try {
                $this->keyword->create((int) $project['id'], $phrase);
                Response::redirect('index.php?route=keyword.list&project=' . $project['id']);
            } catch (\PDOException $e) {
                if ($e->getCode() === '23000') {
                    Response::view('layout', ['content' => fn () => Response::view('keyword/form', [
                        'keyword' => ['phrase' => $phrase],
                        'action' => 'keyword.add&project=' . $project['id'],
                        'projectId' => (int) $project['id'],
                        'error' => 'A keyword with this phrase already exists.',
                    ])]);
                    return;
                }
                throw $e;
            }
        }

        Response::view('layout', ['content' => fn () => Response::view('keyword/form', [
            'keyword' => null,
            'action' => 'keyword.add&project=' . $project['id'],
            'projectId' => (int) $project['id'],
        ])]);
    }

    public function edit(Request $request): void
    {
        $ctx = $this->activeProject((int) $request->query('project', 0));
        if ($ctx === null) {
            $this->renderNoProject();
            return;
        }
        $project = $ctx['project'];
        $id = (int) $request->query('id');
        $keyword = $this->keyword->findOwned($this->userId(), $id, (int) $project['id']);

        if ($keyword === null) {
            Response::redirect('index.php?route=keyword.list&project=' . $project['id']);
        }

        if ($request->isPost()) {
            if (!Csrf::verify((string) $request->post('csrf_token', ''))) {
                Response::redirect('index.php?route=keyword.list&project=' . $project['id']);
                return;
            }
            $phrase = trim((string) $request->post('phrase'));

            if ($phrase === '') {
                Response::view('layout', ['content' => fn () => Response::view('keyword/form', [
                    'keyword' => $keyword,
                    'action' => 'keyword.edit&id=' . $id . '&project=' . $project['id'],
                    'projectId' => (int) $project['id'],
                    'error' => 'Phrase is required.',
                ])]);
                return;
            }

            try {
                $this->keyword->update($id, $phrase);
                Response::redirect('index.php?route=keyword.list&project=' . $project['id']);
            } catch (\PDOException $e) {
                if ($e->getCode() === '23000') {
                    Response::view('layout', ['content' => fn () => Response::view('keyword/form', [
                        'keyword' => ['id' => $id, 'phrase' => $phrase],
                        'action' => 'keyword.edit&id=' . $id . '&project=' . $project['id'],
                        'projectId' => (int) $project['id'],
                        'error' => 'A keyword with this phrase already exists.',
                    ])]);
                    return;
                }
                throw $e;
            }
        }

        Response::view('layout', ['content' => fn () => Response::view('keyword/form', [
            'keyword' => $keyword,
            'action' => 'keyword.edit&id=' . $id . '&project=' . $project['id'],
            'projectId' => (int) $project['id'],
        ])]);
    }

    public function delete(Request $request): void
    {
        $ctx = $this->activeProject((int) $request->query('project', 0));
        if ($ctx === null) {
            $this->renderNoProject();
            return;
        }
        $project = $ctx['project'];
        $id = (int) $request->query('id');

        if ($request->isPost() && Csrf::verify((string) $request->post('csrf_token', ''))) {
            $postId = (int) $request->post('id');
            if ($this->keyword->findOwned($this->userId(), $postId, (int) $project['id']) !== null) {
                $this->keyword->delete($postId);
            }
        }

        Response::redirect('index.php?route=keyword.list&project=' . $project['id']);
    }

    public function export(Request $request): void
    {
        $ctx = $this->activeProject((int) $request->query('project', 0));
        if ($ctx === null) {
            $this->renderNoProject();
            return;
        }
        $project = $ctx['project'];
        $id = (int) $request->query('id');
        $keyword = $this->keyword->findOwned($this->userId(), $id, (int) $project['id']);

        if ($keyword === null) {
            Response::redirect('index.php?route=keyword.list&project=' . $project['id']);
        }

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="minirank-keyword-' . $id . '-history.csv"');

        $out = fopen('php://output', 'wb');
        fputcsv($out, ['Date', 'Position'], ',', '"', '');
        foreach ($this->position->history($id) as $row) {
            fputcsv($out, [$row['captured_at'], (int) $row['position']], ',', '"', '');
        }
        fclose($out);
    }

    public function detail(Request $request): void
    {
        $ctx = $this->activeProject((int) $request->query('project', 0));
        if ($ctx === null) {
            $this->renderNoProject();
            return;
        }
        $project = $ctx['project'];
        $id = (int) $request->query('id');
        $keyword = $this->keyword->findOwned($this->userId(), $id, (int) $project['id']);

        if ($keyword === null) {
            http_response_code(404);
            Response::view('layout', ['content' => fn () => '<p>Keyword not found.</p>']);
            return;
        }

        Response::view('layout', ['content' => fn () => Response::view('keyword/detail', [
            'keyword' => $keyword,
            'position' => $this->position->current($id),
            'history' => $this->position->history($id),
            'projectId' => (int) $project['id'],
        ])]);
    }

    private function renderNoProject(): void
    {
        Response::view('layout', ['content' => fn () => Response::view('project/none', [])]);
    }
}
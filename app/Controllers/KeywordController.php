<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Models\Database;
use App\Models\Keyword;
use App\Models\Position;

class KeywordController
{
    private Keyword $keyword;
    private Position $position;

    public function __construct(?Keyword $keyword = null, ?Position $position = null)
    {
        $this->keyword = $keyword ?? new Keyword(Database::connection());
        $this->position = $position ?? new Position(Database::connection());
    }

    public function list(Request $request): void
    {
        $search = (string) $request->query('q', '');
        $keywords = $this->keyword->all($search);

        Response::view('layout', ['content' => fn () => Response::view('keyword/list', [
            'keywords' => $keywords,
            'search' => $search,
            'trend' => fn (int $id) => $this->position->trend($id),
        ])]);
    }

    public function add(Request $request): void
    {
        if ($request->isPost()) {
            $phrase = trim((string) $request->post('phrase'));

            if ($phrase === '') {
                Response::view('layout', ['content' => fn () => Response::view('keyword/form', [
                    'keyword' => null,
                    'action' => 'keyword.add',
                    'error' => 'Phrase is required.',
                ])]);
                return;
            }

            try {
                $this->keyword->create($phrase);
                Response::redirect('index.php?route=keyword.list');
            } catch (\PDOException $e) {
                if ($e->getCode() === '23000') {
                    Response::view('layout', ['content' => fn () => Response::view('keyword/form', [
                        'keyword' => ['phrase' => $phrase],
                        'action' => 'keyword.add',
                        'error' => 'A keyword with this phrase already exists.',
                    ])]);
                    return;
                }
                throw $e;
            }
        }

        Response::view('layout', ['content' => fn () => Response::view('keyword/form', [
            'keyword' => null,
            'action' => 'keyword.add',
        ])]);
    }

    public function edit(Request $request): void
    {
        $id = (int) $request->query('id');

        if ($request->isPost()) {
            $phrase = trim((string) $request->post('phrase'));

            if ($phrase === '') {
                $keyword = $this->keyword->find($id);
                Response::view('layout', ['content' => fn () => Response::view('keyword/form', [
                    'keyword' => $keyword,
                    'action' => 'keyword.edit&id=' . $id,
                    'error' => 'Phrase is required.',
                ])]);
                return;
            }

            try {
                $this->keyword->update($id, $phrase);
                Response::redirect('index.php?route=keyword.list');
            } catch (\PDOException $e) {
                if ($e->getCode() === '23000') {
                    Response::view('layout', ['content' => fn () => Response::view('keyword/form', [
                        'keyword' => ['id' => $id, 'phrase' => $phrase],
                        'action' => 'keyword.edit&id=' . $id,
                        'error' => 'A keyword with this phrase already exists.',
                    ])]);
                    return;
                }
                throw $e;
            }
        }

        $keyword = $this->keyword->find($id);
        if ($keyword === null) {
            Response::redirect('index.php?route=keyword.list');
        }

        Response::view('layout', ['content' => fn () => Response::view('keyword/form', [
            'keyword' => $keyword,
            'action' => 'keyword.edit&id=' . $id,
        ])]);
    }

    public function delete(Request $request): void
    {
        if ($request->isPost()) {
            $this->keyword->delete((int) $request->post('id'));
        }

        Response::redirect('index.php?route=keyword.list');
    }

    public function detail(Request $request): void
    {
        $id = (int) $request->query('id');
        $keyword = $this->keyword->find($id);

        if ($keyword === null) {
            http_response_code(404);
            Response::view('layout', ['content' => fn () => '<p>Keyword not found.</p>']);
            return;
        }

        Response::view('layout', ['content' => fn () => Response::view('keyword/detail', [
            'keyword' => $keyword,
            'history' => $this->position->history($id),
        ])]);
    }
}

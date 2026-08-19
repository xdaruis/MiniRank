<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Models\Database;
use App\Models\User;

class AuthController
{
    private User $user;

    public function __construct(?User $user = null)
    {
        $this->user = $user ?? new User(Database::connection());
    }

    public function login(Request $request): void
    {
        if ($request->isPost()) {
            if (!$request->validCsrf()) {
                Response::view('layout', ['content' => fn () => Response::view('auth/login', ['error' => 'Session expired. Try again.'])]);
                return;
            }

            $username = trim((string) $request->post('username', ''));
            $password = (string) $request->post('password', '');
            $user = $username !== '' ? $this->user->findByUsername($username) : null;

            if ($user !== null && password_verify($password, $user['password_hash'])) {
                Auth::login((int) $user['id']);
                Response::redirect('index.php?route=keyword.list');
            }

            Response::view('layout', ['content' => fn () => Response::view('auth/login', [
                'error' => 'Invalid username or password.',
            ])]);
            return;
        }

        Response::view('layout', ['content' => fn () => Response::view('auth/login', [])]);
    }

    public function register(Request $request): void
    {
        if ($request->isPost()) {
            if (!$request->validCsrf()) {
                Response::view('layout', ['content' => fn () => Response::view('auth/register', ['error' => 'Session expired. Try again.'])]);
                return;
            }

            $username = trim((string) $request->post('username', ''));
            $password = (string) $request->post('password', '');

            if ($username === '' || $password === '') {
                Response::view('layout', ['content' => fn () => Response::view('auth/register', ['error' => 'Username and password are required.'])]);
                return;
            }

            if (strlen($password) < 8) {
                Response::view('layout', ['content' => fn () => Response::view('auth/register', ['error' => 'Password must be at least 8 characters.'])]);
                return;
            }

            try {
                $id = $this->user->create($username, $password);
            } catch (\PDOException $e) {
                if ($e->getCode() === '23000') {
                    Response::view('layout', ['content' => fn () => Response::view('auth/register', ['error' => 'That username is already taken.'])]);
                    return;
                }
                throw $e;
            }

            Auth::login($id);
            Response::redirect('index.php?route=keyword.list');
            return;
        }

        Response::view('layout', ['content' => fn () => Response::view('auth/register', [])]);
    }

    public function logout(Request $request): void
    {
        if ($request->isPost() && $request->validCsrf()) {
            Auth::logout();
        }
        Response::redirect('index.php?route=auth.login');
    }
}
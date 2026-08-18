<?php

declare(strict_types=1);

namespace App\Tests\Http;

use App\Models\User;
use App\Tests\Support\HttpTestCase;
use App\Tests\Support\RedirectSignal;

class AuthFlowTest extends HttpTestCase
{
    protected function post(string $route, array $fields, string $csrf = 'valid-token'): void
    {
        $_SESSION['csrf'] = $csrf;
        $this->setMethod('POST');
        $_POST = ['csrf_token' => $csrf] + $fields;
        $this->runRoute($route);
    }

    public function testLoginSuccessSetsSessionAndRedirects(): void
    {
        $userId = $this->seedUser('alice');

        $this->setMethod('GET');
        ob_start();
        $this->runRoute('auth.login');
        ob_get_clean();

        $signal = null;
        try {
            $this->post('auth.login', ['username' => 'alice', 'password' => 'password']);
        } catch (RedirectSignal $e) {
            $signal = $e;
        }

        $this->assertNotNull($signal);
        $this->assertStringContainsString('keyword.list', $signal->url);
        $this->assertSame($userId, $_SESSION['user_id']);
    }

    public function testLoginWrongPasswordShowsError(): void
    {
        $this->seedUser('alice');

        ob_start();
        $this->post('auth.login', ['username' => 'alice', 'password' => 'nope']);
        $output = ob_get_clean();

        $this->assertSame(200, http_response_code());
        $this->assertStringContainsString('Invalid username or password.', $output);
        $this->assertArrayNotHasKey('user_id', $_SESSION);
    }

    public function testLoginUnknownUserShowsError(): void
    {
        ob_start();
        $this->post('auth.login', ['username' => 'ghost', 'password' => 'password']);
        $output = ob_get_clean();

        $this->assertStringContainsString('Invalid username or password.', $output);
        $this->assertArrayNotHasKey('user_id', $_SESSION);
    }

    public function testLoginMissingCsrfShowsSessionError(): void
    {
        $this->setMethod('POST');
        $_POST = ['csrf_token' => '', 'username' => 'alice', 'password' => 'password'];

        ob_start();
        $this->runRoute('auth.login');
        $output = ob_get_clean();

        $this->assertStringContainsString('Session expired. Try again.', $output);
        $this->assertArrayNotHasKey('user_id', $_SESSION);
    }

    public function testLoginGetRendersForm(): void
    {
        $this->setMethod('GET');
        ob_start();
        $this->runRoute('auth.login');
        $output = ob_get_clean();

        $this->assertSame(200, http_response_code());
        $this->assertStringContainsString('Log in', $output);
        $this->assertArrayNotHasKey('user_id', $_SESSION);
    }

    public function testRegisterSuccessCreatesAndLogsIn(): void
    {
        $this->setMethod('GET');
        ob_start();
        $this->runRoute('auth.register');
        ob_get_clean();

        $signal = null;
        try {
            $this->post('auth.register', ['username' => 'carol', 'password' => 'secret-pass']);
        } catch (RedirectSignal $e) {
            $signal = $e;
        }

        $this->assertNotNull($signal);
        $this->assertStringContainsString('keyword.list', $signal->url);

        $user = (new User($this->pdo))->findByUsername('carol');
        $this->assertNotNull($user);
        $this->assertSame($user['id'], $_SESSION['user_id']);
    }

    public function testRegisterShortPasswordRejected(): void
    {
        $this->setMethod('GET');
        ob_start();
        $this->runRoute('auth.register');
        ob_get_clean();

        ob_start();
        $this->post('auth.register', ['username' => 'carol', 'password' => 'short']);
        $output = ob_get_clean();

        $this->assertStringContainsString('at least 8 characters', $output);
        $this->assertNull((new User($this->pdo))->findByUsername('carol'));
        $this->assertArrayNotHasKey('user_id', $_SESSION);
    }

    public function testRegisterEmptyFieldsRejected(): void
    {
        ob_start();
        $this->post('auth.register', ['username' => '', 'password' => '']);
        $output = ob_get_clean();

        $this->assertStringContainsString('required', $output);
        $stmt = $this->pdo->query('SELECT COUNT(*) FROM users');
        $this->assertSame(0, (int) $stmt->fetchColumn());
    }

    public function testRegisterDuplicateUsernameRejected(): void
    {
        $this->seedUser('alice');

        ob_start();
        $this->post('auth.register', ['username' => 'alice', 'password' => 'secret-pass']);
        $output = ob_get_clean();

        $this->assertStringContainsString('already taken', $output);
        $this->assertArrayNotHasKey('user_id', $_SESSION);
    }

    public function testLogoutPostClearsSession(): void
    {
        $userId = $this->seedUser('alice');
        $this->setUpAuth($userId);

        $signal = null;
        try {
            $this->post('auth.logout', []);
        } catch (RedirectSignal $e) {
            $signal = $e;
        }

        $this->assertNotNull($signal);
        $this->assertStringContainsString('auth.login', $signal->url);
        $this->assertArrayNotHasKey('user_id', $_SESSION);
    }

    public function testLogoutGetIsNoop(): void
    {
        $userId = $this->seedUser('alice');
        $this->setUpAuth($userId);

        $this->setMethod('GET');
        $signal = null;
        try {
            $this->runRoute('auth.logout');
        } catch (RedirectSignal $e) {
            $signal = $e;
        }

        $this->assertNotNull($signal);
        $this->assertSame($userId, $_SESSION['user_id']);
    }
}
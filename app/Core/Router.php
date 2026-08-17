<?php

declare(strict_types=1);

namespace App\Core;

class Router
{
    public function dispatch(Request $request, string $defaultRoute = 'keyword.list'): void
    {
        $route = $request->query('route', $defaultRoute);
        if (!str_starts_with($route, 'auth.') && Auth::userId() === null) {
            Response::redirect('index.php?route=auth.login');
        }

        [$controller, $action] = $this->resolve($route);

        $controller->{$action}($request);
    }

    private function resolve(string $route): array
    {
        // Route -> [controllerClass, action]
        $map = [
            'auth.login' => ['App\\Controllers\\AuthController', 'login'],
            'auth.register' => ['App\\Controllers\\AuthController', 'register'],
            'auth.logout' => ['App\\Controllers\\AuthController', 'logout'],
            'keyword.list' => ['App\\Controllers\\KeywordController', 'list'],
            'keyword.add' => ['App\\Controllers\\KeywordController', 'add'],
            'keyword.edit' => ['App\\Controllers\\KeywordController', 'edit'],
            'keyword.delete' => ['App\\Controllers\\KeywordController', 'delete'],
            'keyword.detail' => ['App\\Controllers\\KeywordController', 'detail'],
            'keyword.export' => ['App\\Controllers\\KeywordController', 'export'],
            'position.refresh' => ['App\\Controllers\\PositionController', 'refresh'],
        ];

        $entry = $map[$route] ?? $map['keyword.list'];

        return [new $entry[0](), $entry[1]];
    }
}
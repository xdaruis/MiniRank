<?php

declare(strict_types=1);

namespace App\Core;

class Router
{
    public function dispatch(Request $request): void
    {
        $route = $request->query('route', 'keyword.list');
        [$controller, $action] = $this->resolve($route);

        $controller->{$action}($request);
    }

    private function resolve(string $route): array
    {
        // Route -> [controllerClass, action]
        $map = [
            'keyword.list' => ['App\\Controllers\\KeywordController', 'list'],
            'keyword.add' => ['App\\Controllers\\KeywordController', 'add'],
            'keyword.edit' => ['App\\Controllers\\KeywordController', 'edit'],
            'keyword.delete' => ['App\\Controllers\\KeywordController', 'delete'],
            'keyword.detail' => ['App\\Controllers\\KeywordController', 'detail'],
            'position.refresh' => ['App\\Controllers\\PositionController', 'refresh'],
        ];

        $entry = $map[$route] ?? $map['keyword.list'];

        return [new $entry[0](), $entry[1]];
    }
}

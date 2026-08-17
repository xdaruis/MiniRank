<?php

declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';

use App\Core\Request;
use App\Core\Router;

// TODO(S2/S3): refresh.php doubles as a mini-router because Router reads ?route= from the
// query and the default only applies when the param is absent. A POST to
// refresh.php?route=keyword.delete&id=N would trigger KeywordController::delete via this
// endpoint. Not exploitable in a single-user app, but a design smell once S2 (multi-project)
// or S3 (accounts/CSRF) add more POST actions. Fix then: force 'position.refresh' and ignore
// ?route= (e.g. call PositionController::refresh directly instead of dispatching the Router).
$router = new Router();
$router->dispatch(new Request(), 'position.refresh');

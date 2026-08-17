<?php

declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';

use App\Controllers\PositionController;
use App\Core\Auth;
use App\Core\Request;

if (Auth::userId() === null) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

(new PositionController())->refresh(new Request());
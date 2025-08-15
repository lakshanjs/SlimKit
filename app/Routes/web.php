<?php

declare(strict_types=1);

use Slim\App;
use Slim\Routing\RouteCollectorProxy;
use App\Controllers\AuthController;
use App\Controllers\FileController;
use App\Middleware\AuthMiddleware;

return function (App $app): void {
    $app->get('/', function ($request, $response) {
        $nonce = $request->getAttribute('cspNonce');
        $response->getBody()->write('<h1>Welcome to SlimKit</h1>');
        return $response;
    });

    $app->get('/login', [AuthController::class, 'showLogin']);
    $app->post('/login', [AuthController::class, 'login']);
    $app->get('/logout', [AuthController::class, 'logout']);

    $app->group('', function (RouteCollectorProxy $group) {
        $group->get('/files/upload', [FileController::class, 'showForm']);
        $group->post('/files/upload', [FileController::class, 'upload']);
    })->add(AuthMiddleware::class);

    $app->get('/api/ping', function ($request, $response) {
        $response->getBody()->write(json_encode(['ping' => 'pong']));
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->post('/api/files', [FileController::class, 'apiUpload']);
};

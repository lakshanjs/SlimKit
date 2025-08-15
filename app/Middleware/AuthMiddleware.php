<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Nyholm\Psr7\Response;

class AuthMiddleware
{
    public function __construct(private string $loginRoute = '/login')
    {
    }

    public function __invoke(Request $request, Handler $handler): ResponseInterface
    {
        if (!isset($_SESSION['user_id'])) {
            $response = new Response();
            return $response->withHeader('Location', $this->loginRoute)->withStatus(302);
        }

        return $handler->handle($request);
    }
}

<?php
declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Slim\Psr7\Response;

class AuthMiddleware
{
    public function __construct(private string $loginRoute = '/login')
    {
    }

    public function __invoke(Request $request, Handler $handler): Response
    {
        if (!isset($_SESSION['user_id'])) {
            $response = new Response();
            return $response->withHeader('Location', $this->loginRoute)->withStatus(302);
        }

        return $handler->handle($request);
    }
}

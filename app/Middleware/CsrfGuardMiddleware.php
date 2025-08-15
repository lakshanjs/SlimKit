<?php

declare(strict_types=1);

namespace App\Middleware;

use Slim\Csrf\Guard;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface as Handler;

class CsrfGuardMiddleware
{
    public function __construct(private Guard $guard)
    {
    }

    public function __invoke(Request $request, Handler $handler): Response
    {
        return $this->guard->process($request, $handler);
    }

    public function getToken(): array
    {
        $token = $this->guard->generateToken();
        return [
            'nameKey' => $this->guard->getTokenNameKey(),
            'valueKey' => $this->guard->getTokenValueKey(),
            'name' => $token[$this->guard->getTokenNameKey()],
            'value' => $token[$this->guard->getTokenValueKey()],
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Middleware;

use Bepsvpt\SecureHeaders\SecureHeaders;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as Handler;

class SecureHeadersMiddleware implements MiddlewareInterface
{
    public function __construct(private SecureHeaders $headers)
    {
    }

    public function process(Request $request, Handler $handler): Response
    {
        $response = $handler->handle($request);
        foreach ($this->headers->headers() as $name => $value) {
            $response = $response->withHeader($name, $value);
        }
        return $response;
    }
}

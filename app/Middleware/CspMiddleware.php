<?php
declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Psr\Http\Message\ResponseInterface as Response;

class CspMiddleware implements MiddlewareInterface
{
    public function process(Request $request, Handler $handler): Response
    {
        $nonce = base64_encode(random_bytes(16));
        $request = $request->withAttribute('cspNonce', $nonce);
        $response = $handler->handle($request);
        $policy = "default-src 'self'; script-src 'self' 'nonce-{$nonce}'; style-src 'self' 'nonce-{$nonce}'; object-src 'none'; base-uri 'self';";
        return $response->withHeader('Content-Security-Policy', $policy);
    }
}

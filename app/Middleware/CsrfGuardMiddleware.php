<?php
declare(strict_types=1);

namespace App\Middleware;

use Selective\Csrf\CsrfMiddleware;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface as Handler;

class CsrfGuardMiddleware
{
    public function __construct(private CsrfMiddleware $csrf)
    {
    }

    public function __invoke(Request $request, Handler $handler): Response
    {
        return $this->csrf->process($request, $handler);
    }

    public function getToken(): array
    {
        return [
            'nameKey' => $this->csrf->getTokenNameKey(),
            'valueKey' => $this->csrf->getTokenValueKey(),
            'name' => $this->csrf->getTokenName(),
            'value' => $this->csrf->getTokenValue(),
        ];
    }
}

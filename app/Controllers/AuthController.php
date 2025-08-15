<?php
declare(strict_types=1);

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use App\Services\Auth;
use App\Support\Validation;
use Respect\Validation\Validator as v;

class AuthController
{
    public function __construct(private Twig $view, private Auth $auth)
    {
    }

    public function showLogin(Request $request, Response $response): Response
    {
        $nonce = $request->getAttribute('cspNonce');
        return $this->view->render($response, 'auth/login.twig', ['csp_nonce' => $nonce]);
    }

    public function login(Request $request, Response $response): Response
    {
        $data = (array)$request->getParsedBody();
        $errors = Validation::validate($data, [
            'username' => v::notEmpty()->alnum()->noWhitespace(),
            'password' => v::notEmpty(),
        ]);

        $nonce = $request->getAttribute('cspNonce');

        if ($errors) {
            return $this->view->render($response->withStatus(422), 'auth/login.twig', [
                'errors' => $errors,
                'csp_nonce' => $nonce,
            ]);
        }

        if ($this->auth->attempt($data['username'], $data['password'])) {
            return $response->withHeader('Location', '/')->withStatus(302);
        }

        return $this->view->render($response->withStatus(401), 'auth/login.twig', [
            'error' => 'Invalid credentials.',
            'csp_nonce' => $nonce,
        ]);
    }

    public function logout(Request $request, Response $response): Response
    {
        $this->auth->logout();
        return $response->withHeader('Location', '/login')->withStatus(302);
    }
}

<?php

declare(strict_types=1);

use DI\ContainerBuilder;
use Slim\Factory\AppFactory;
use Dotenv\Dotenv;
use Tuupola\Middleware\CorsMiddleware;
use Bepsvpt\SecureHeaders\SecureHeaders;
use App\Middleware\SecureHeadersMiddleware;
use App\Middleware\CspMiddleware;
use App\Middleware\CsrfGuardMiddleware;
use Throwable;
use Nyholm\Psr7\Response;

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

$containerBuilder = new ContainerBuilder();
$settings = require __DIR__ . '/../config/settings.php';
$containerBuilder->addDefinitions(['settings' => $settings]);
$dependencies = require __DIR__ . '/../config/dependencies.php';
$containerBuilder->addDefinitions($dependencies);
$container = $containerBuilder->build();

AppFactory::setContainer($container);
$app = AppFactory::create();

$session = $settings['session'];
session_name($session['name']);
session_set_cookie_params([
    'httponly' => true,
    'secure' => true,
    'samesite' => 'Lax',
]);
session_start();

$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();

$app->add(new CorsMiddleware());
$secureHeaders = new SecureHeaders([
    'hsts' => [
        'enable' => true,
        'max-age' => 31536000,
        'include-sub-domains' => true,
    ],
    'csp' => ['enable' => false],
]);
$app->add(new SecureHeadersMiddleware($secureHeaders));
$app->add(CspMiddleware::class);
$app->add($container->get(CsrfGuardMiddleware::class));

$errorMiddleware = $app->addErrorMiddleware(
    $settings['displayErrorDetails'],
    true,
    true
);
$errorMiddleware->setDefaultErrorHandler(function ($request, Throwable $exception) {
    $response = new Response(500);
    $response->getBody()->write('An internal error occurred.');
    return $response;
});

$twigInit = require __DIR__ . '/../config/twig.php';
$twigInit($container);

$routes = require __DIR__ . '/Routes/web.php';
$routes($app);

return $app;

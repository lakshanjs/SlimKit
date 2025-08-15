<?php
declare(strict_types=1);

use DI\ContainerBuilder;
use Slim\Factory\AppFactory;
use Dotenv\Dotenv;
use Middlewares\Cors;
use Middlewares\SecureHeaders;
use App\Middleware\CspMiddleware;
use App\Middleware\CsrfGuardMiddleware;
use Psr\Container\ContainerInterface;
use Monolog\Logger;
use Throwable;
use Slim\Psr7\Response;

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

$app->add(new Cors());
$app->add(new SecureHeaders([
    'content-security-policy' => false,
    'strict-transport-security' => 'max-age=31536000; includeSubDomains',
]));
$app->add(CspMiddleware::class);
$app->add($container->get(CsrfGuardMiddleware::class));

$errorMiddleware = $app->addErrorMiddleware(
    $settings['displayErrorDetails'],
    true,
    true
);
$errorMiddleware->setDefaultErrorHandler(function ($request, Throwable $exception) use ($container) {
    $logger = $container->get(Logger::class);
    $logger->error($exception->getMessage(), ['exception' => $exception]);
    $response = new Response(500);
    $response->getBody()->write('An internal error occurred.');
    return $response;
});

$twigInit = require __DIR__ . '/../config/twig.php';
$twigInit($container);

$routes = require __DIR__ . '/Routes/web.php';
$routes($app);

return $app;

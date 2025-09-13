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
use Nyholm\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface;
use Whoops\Handler\JsonResponseHandler;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

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
// Dynamically determine the application's base path 
$basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
$app->setBasePath($basePath);

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

$defaultSecureHeadersConfig = require __DIR__ . '/../vendor/bepsvpt/secure-headers/config/secure-headers.php';

$secureHeadersConfig = array_replace_recursive($defaultSecureHeadersConfig, [
    'hsts' => [
        'enable' => true,
        'max-age' => 31536000,
        'include-sub-domains' => true,
    ],
    'csp' => ['enable' => false],
]);

$secureHeaders = new SecureHeaders($secureHeadersConfig);
$app->add(new SecureHeadersMiddleware($secureHeaders));
$app->add(CspMiddleware::class);
$app->add($container->get(CsrfGuardMiddleware::class));

$errorMiddleware = $app->addErrorMiddleware(
    $settings['displayErrorDetails'],
    true,
    true
);
$errorMiddleware->setDefaultErrorHandler(function (ServerRequestInterface $request, \Throwable $exception) use ($container) {
    $response = new Response(500);

    $env = $container->get('settings')['env'] ?? 'production';

    if ($env === 'development') {
        $whoops = new Run();
        if ($request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest') {
            $handler = new JsonResponseHandler();
            $handler->addTraceToOutput(true);
        } else {
            $handler = new PrettyPageHandler();
        }

        $whoops->pushHandler($handler);
        $whoops->allowQuit(false);
        $whoops->writeToOutput(false);

        $response->getBody()->write($whoops->handleException($exception));
        $contentType = $handler instanceof JsonResponseHandler ? 'application/json' : 'text/html';

        return $response->withHeader('Content-Type', $contentType);
    }

    $response->getBody()->write('An internal error occurred.');

    return $response;
});

$twigInit = require __DIR__ . '/../config/twig.php';
$twigInit($container);

$routes = require __DIR__ . '/Routes/web.php';
$routes($app);

return $app;

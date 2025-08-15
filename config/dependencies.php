<?php
declare(strict_types=1);

use DI\Container;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Nyholm\Psr7\Factory\Psr17Factory;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Processor\UidProcessor;
use Monolog\Processor\PsrLogMessageProcessor;
use Slim\Views\Twig;
use App\Middleware\CsrfGuardMiddleware;
use App\View\TwigExtensions;
use Selective\Csrf\CsrfMiddleware;

return [
    Psr17Factory::class => fn() => new Psr17Factory(),
    ResponseFactoryInterface::class => fn(ContainerInterface $c) => $c->get(Psr17Factory::class),
    StreamFactoryInterface::class => fn(ContainerInterface $c) => $c->get(Psr17Factory::class),

    PDO::class => function (ContainerInterface $c) {
        $settings = $c->get('settings')['db'];
        $pdo = new PDO($settings['dsn'], $settings['username'], $settings['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        return $pdo;
    },

    Logger::class => function (ContainerInterface $c) {
        $logPath = $c->get('settings')['logPath'];
        $logger = new Logger('app');
        $handler = new StreamHandler($logPath, Logger::DEBUG);
        $logger->pushHandler($handler);
        $logger->pushProcessor(new UidProcessor());
        $logger->pushProcessor(new PsrLogMessageProcessor());
        $logger->pushProcessor(function (array $record) {
            foreach (['password', 'token'] as $key) {
                if (isset($record['context'][$key])) {
                    $record['context'][$key] = '[redacted]';
                }
            }
            return $record;
        });
        return $logger;
    },

    Twig::class => function (ContainerInterface $c) {
        $settings = $c->get('settings')['twig'];
        return Twig::create($settings['paths'], ['cache' => $settings['cache']]);
    },

    CsrfGuardMiddleware::class => function (ContainerInterface $c) {
        $csrf = new CsrfMiddleware($c->get(ResponseFactoryInterface::class));
        return new CsrfGuardMiddleware($csrf);
    },

    TwigExtensions::class => fn(ContainerInterface $c) => new TwigExtensions($c->get(CsrfGuardMiddleware::class)),
];

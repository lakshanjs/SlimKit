<?php

declare(strict_types=1);

use DI\Container;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Nyholm\Psr7\Factory\Psr17Factory;
use Slim\Views\Twig;
use App\Middleware\CsrfGuardMiddleware;
use App\View\TwigExtensions;
use Slim\Csrf\Guard;

return [
    Psr17Factory::class => fn () => new Psr17Factory(),
    ResponseFactoryInterface::class => fn (ContainerInterface $c) => $c->get(Psr17Factory::class),
    StreamFactoryInterface::class => fn (ContainerInterface $c) => $c->get(Psr17Factory::class),

    PDO::class => function (ContainerInterface $c) {
        $settings = $c->get('settings')['db'];
        $pdo = new PDO($settings['dsn'], $settings['username'], $settings['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        return $pdo;
    },


    Twig::class => function (ContainerInterface $c) {
        $settings = $c->get('settings')['twig'];
        return Twig::create($settings['paths'], ['cache' => $settings['cache']]);
    },

    CsrfGuardMiddleware::class => function (ContainerInterface $c) {
        $guard = new Guard($c->get(ResponseFactoryInterface::class));
        return new CsrfGuardMiddleware($guard);
    },

    TwigExtensions::class => fn (ContainerInterface $c) => new TwigExtensions($c->get(CsrfGuardMiddleware::class)),
];

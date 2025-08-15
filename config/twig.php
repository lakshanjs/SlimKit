<?php
declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Slim\Views\Twig;
use App\View\TwigExtensions;

return function (ContainerInterface $container): void {
    $twig = $container->get(Twig::class);
    $twig->addExtension($container->get(TwigExtensions::class));
};

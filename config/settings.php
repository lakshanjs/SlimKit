<?php
return [
    'env' => $_ENV['APP_ENV'] ?? 'production',
    'debug' => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOL),
    'displayErrorDetails' => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOL),
    'logPath' => __DIR__ . '/../' . ($_ENV['LOG_PATH'] ?? 'var/logs/app.log'),
    'db' => [
        'dsn' => $_ENV['DB_DSN'] ?? '',
        'username' => $_ENV['DB_USER'] ?? '',
        'password' => $_ENV['DB_PASS'] ?? '',
    ],
    'twig' => [
        'paths' => [__DIR__ . '/../templates'],
        'cache' => __DIR__ . '/../storage/cache',
    ],
    'session' => [
        'name' => $_ENV['SESSION_NAME'] ?? 'slimkit_session',
    ],
];

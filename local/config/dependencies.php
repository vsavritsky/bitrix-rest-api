<?php

declare(strict_types=1);

use App\Repository\User\UserRepository;
use BitrixRestApi\Storage\Bitrix;
use DI\ContainerBuilder;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use OAuth2\Server;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use OAuth2\GrantType;
use Bitrix\Main\Config;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        LoggerInterface::class => function (ContainerInterface $c) {

            $settings = $c->get(\BitrixRestApi\Settings\SettingsInterface::class);

            $loggerSettings = $settings->get('logger');
            $logger = new Logger($loggerSettings['name']);

            $processor = new UidProcessor();
            $logger->pushProcessor($processor);

            $handler = new StreamHandler($loggerSettings['path'], $loggerSettings['level']);
            $logger->pushHandler($handler);

            return $logger;
        },
        Server::class => function (ContainerInterface $c) {
            $configuration = Config\Configuration::getInstance();
            $configDb = $configuration->getValue('connections')['default'];

            $pdo = new \PDO('mysql:host=' . $configDb['host'] . ';dbname=' . $configDb['database'], $configDb['login'], $configDb['password']);

            $storage = new Bitrix($pdo);

            return new Server($storage,
                [
                    'access_lifetime' => 3600, // 1 час
                    'refresh_token_lifetime' => 2592000, // 30 дней
                ],
                [
                    new GrantType\UserCredentials($storage),
                    new GrantType\RefreshToken($storage, [
                        'always_issue_new_refresh_token' => true,
                        'unset_refresh_token_after_use' => true,
                    ]),
                ]
            );
        },
        \BitrixRestApi\Middleware\CacheMiddleware::class => function (ContainerInterface $c) {
            return new \BitrixRestApi\Middleware\CacheMiddleware();
        },
        \Chadicus\Slim\OAuth2\Middleware\Authorization::class => function (ContainerInterface $c) {
            return new Chadicus\Slim\OAuth2\Middleware\Authorization($c->get(\OAuth2\Server::class), []);
        },
        App\Service\Security\RegistrationService::class => function (ContainerInterface $c) {
            return new App\Service\Security\RegistrationService(new UserRepository(), []);
        },
        App\Service\Security\RestoreService::class => function (ContainerInterface $c) {
            return new App\Service\Security\RestoreService(new UserRepository(), []);
        },
        \App\Response\Favorite\FavoriteResponse::class => function (ContainerInterface $c) {
            return new \App\Response\Favorite\FavoriteResponse(new \App\Repository\Catalog\ProductRepository(), []);
        },
    ]);
};

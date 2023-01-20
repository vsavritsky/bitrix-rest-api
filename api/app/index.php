<?php

include($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

use BitrixRestApi\Handler\HttpErrorHandler;
use BitrixRestApi\Handler\ShutdownHandler;
use BitrixRestApi\ResponseEmitter\ResponseEmitter;
use BitrixRestApi\Settings\SettingsInterface;
use DI\ContainerBuilder;
use Slim\Factory\AppFactory;
use Slim\Factory\ServerRequestCreatorFactory;

// Instantiate PHP-DI ContainerBuilder
$containerBuilder = new ContainerBuilder();

// Set up settings
$settings = require $_SERVER["DOCUMENT_ROOT"] . '/local/config/settings.php';
$settings($containerBuilder);

// Set up dependencies
$dependencies = require $_SERVER["DOCUMENT_ROOT"] . '/local/config/dependencies.php';
$dependencies($containerBuilder);

// Build PHP-DI Container instance
$container = $containerBuilder->build();

// Set up repositories
$repositories = require $_SERVER["DOCUMENT_ROOT"] . '/local/config/repositories.php';
$repositories($container);

// Instantiate the app
AppFactory::setContainer($container);
$app = AppFactory::create();
$callableResolver = $app->getCallableResolver();

// Register middleware
$middleware = require $_SERVER["DOCUMENT_ROOT"] . '/local/config/middleware.php';
$middleware($app);

// Register routes
$routes = require $_SERVER["DOCUMENT_ROOT"] . '/local/config/routes.php';
$routes($app);

//$settings = $container->get(SettingsInterface::class);
//
//$displayErrorDetails = $settings->get('displayErrorDetails');
//$logError = $settings->get('logError');
//$logErrorDetails = $settings->get('logErrorDetails');

// Create Request object from globals
$serverRequestCreator = ServerRequestCreatorFactory::create();
$request = $serverRequestCreator->createServerRequestFromGlobals();

// Create Error Handler
$responseFactory = $app->getResponseFactory();
$errorHandler = new HttpErrorHandler($callableResolver, $responseFactory);

// Create Shutdown Handler
$shutdownHandler = new ShutdownHandler($request, $errorHandler, true);
register_shutdown_function($shutdownHandler);

// Add Routing Middleware
$app->addRoutingMiddleware();

// Add Body Parsing Middleware
$app->addBodyParsingMiddleware();

// Add Error Middleware
$errorMiddleware = $app->addErrorMiddleware(true, true, true);
$errorMiddleware->setDefaultErrorHandler($errorHandler);

// Run App & Emit Response
$response = $app->handle($request);
$responseEmitter = new ResponseEmitter();
$responseEmitter->emit($response);

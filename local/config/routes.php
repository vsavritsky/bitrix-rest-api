<?php

declare(strict_types=1);


use Slim\App;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Chadicus\Slim\OAuth2\Routes\Revoke;
use Chadicus\Slim\OAuth2\Routes\Token;

return function (App $app) {
    $container = $app->getContainer();

    $server = $container->get(\OAuth2\Server::class);
    $cacheMiddleware = $container->get(\BitrixRestApi\Middleware\CacheMiddleware::class);
    $authMiddleware = $container->get(\Chadicus\Slim\OAuth2\Middleware\Authorization::class);

    //$app->post('/api/app/security/token', new Token($server));
    //$app->post('/api/app/security/revoke', new Revoke($server));
    //$app->post('/api/security/registration/byEmail', [\App\Controller\SecurityController::class, "registrationByEmail"]);
    //$app->post('/api/app/security/restore/byEmail', [\App\Controller\SecurityController::class, "restoreByEmail"]);
    //$app->post('/api/app/security/restore/changePassword', [\App\Controller\SecurityController::class, "restoreChangePassword"]);

    #$app->get('/api/app/personal/profile/item', [\App\Controller\PersonalController::class, "profileItem"])->addMiddleware($authMiddleware);
    #$app->post('/api/app/personal/profile/edit', [\App\Controller\PersonalController::class, "profileEdit"])->addMiddleware($authMiddleware);
    
    $app->get('/api/app/settings/view', [\App\Controller\SettingsController::class, "view"])->add($cacheMiddleware);

    $app->get('/api/app/content/news/list', [\App\Controller\NewsController::class, "list"])->add($cacheMiddleware);
    $app->get('/api/app/content/news/{id}/view', [\App\Controller\NewsController::class, "view"])->add($cacheMiddleware);

    //->addMiddleware($authMiddleware)


};

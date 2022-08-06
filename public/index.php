<?php

use TryAgainLater\TodoApp\App;
use TryAgainLater\TodoApp\Controllers\{UserController, SessionController};

require_once '../app/bootstrap.php';

$router
    ->onNotFound(static function (App $app) {
        http_response_code(404);
        return $app->view->render('errors/404.twig');
    })

    ->get('/', static function (App $app) {
        return $app->view->render('main/index.twig');
    })

    ->get('signup', UserController::create(...))
    ->post('signup', UserController::store(...))

    ->get('login', SessionController::create(...))
    ->post('login', SessionController::store(...))
    ->post('logout', SessionController::destroy(...));

$response = $router->resolve($app);
if (!empty($response)) {
    echo $response;
}

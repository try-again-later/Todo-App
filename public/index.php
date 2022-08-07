<?php

use TryAgainLater\TodoApp\{App, Request, Router};
use TryAgainLater\TodoApp\Controllers\{UserController, SessionController};
use TryAgainLater\TodoApp\Models\User;

require_once '../app/bootstrap.php';

$request = new Request(serverArray: $_SERVER, getArray: $_GET, postArray: $_POST);

$router = new Router($request);

$app = new App(
    database: $database,
    view: $twig,
    request: $request,
);

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

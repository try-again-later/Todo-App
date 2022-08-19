<?php

declare(strict_types = 1);

use TryAgainLater\TodoApp\{App, Request, Router};
use TryAgainLater\TodoApp\Controllers\{UserController, SessionController, TodoController};

$bootstrapResult = require_once '../app/bootstrap.php';
if (!$bootstrapResult) {
    http_response_code(500);
    return;
}

try {
    $request = new Request(serverArray: $_SERVER, getArray: $_GET, postArray: $_POST);

    $router = new Router($request);

    $app = new App(
        database: $database,
        twig: $twig,
        request: $request,
    );

    $router
        ->onNotFound(static function (App $app) {
            http_response_code(404);
            return $app->view->render('errors/404');
        })

        ->get('/', static function (App $app) {
            return $app->view->render(
                'main/index',
                [
                    'signUpSuccess' =>
                        $app->getFlashMessage(UserController::SIGN_UP_SUCCESSFUL_MESSAGE_KEY)
                ],
            );
        })

        ->get('signup', UserController::create(...))
        ->post('signup', UserController::store(...))

        ->get('login', SessionController::create(...))
        ->post('login', SessionController::store(...))
        ->post('logout', SessionController::destroy(...))

        ->get('todos', TodoController::index(...), auth: true)
        ->get('todos/create', TodoController::create(...), auth: true)
        ->post('todos', TodoController::store(...), auth: true)
        ->get('todos/(?<id>\\d+)/edit', TodoController::edit(...), auth: true)
        ->post('todos/(?<id>\\d+)', TodoController::update(...), auth: true)
        ->post('todos/(?<id>\\d+)/destroy', TodoController::destroy(...), auth: true);

    $response = $router->resolve($app);
    if (!empty($response)) {
        echo $response;
    }

    unset($_SESSION['last-user-input']);
} catch (Exception $e) {
    echo $twig->render('errors/500.twig');
    throw $e;
}

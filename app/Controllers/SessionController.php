<?php

declare(strict_types = 1);

namespace TryAgainLater\TodoApp\Controllers;

use TryAgainLater\TodoApp\App;

class SessionController
{
    public static function create(App $app): string
    {
        return $app->view->render('session/create.twig');
    }

    public static function store(App $app)
    {}

    public static function destroy(App $app)
    {}
}

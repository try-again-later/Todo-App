<?php

declare(strict_types = 1);

namespace TryAgainLater\TodoApp\Controllers;

use TryAgainLater\TodoApp\App;

class UserController
{
    public static function create(App $app)
    {
        return $app->view->render('user/create.twig');
    }

    public static function store(App $app)
    {}
}

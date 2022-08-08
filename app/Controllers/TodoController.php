<?php

declare(strict_types = 1);

namespace TryAgainLater\TodoApp\Controllers;

use TryAgainLater\TodoApp\App;

class TodoController
{
    public static function index(App $app): ?string
    {
        if (!isset($_SESSION['user-email'])) {
            header('Location: ' . '/login');
            return null;
        }

        return $app->view->render(
            'todos/index.twig',
            [
                'csrfToken' => $_SESSION['csrf-token'] ?? '',
                'userEmail' => $_SESSION['user-email'],
            ],
        );
    }

    public static function create(App $app): ?string
    {
        if (!isset($_SESSION['user-email'])) {
            header('Location: ' . '/login');
            return null;
        }

        return $app->view->render(
            'todos/create.twig',
            [
                'csrfToken' => $_SESSION['csrf-token'] ?? '',
                'userEmail' => $_SESSION['user-email'],
            ],
        );
    }

    public static function store(App $app)
    {
        if (!isset($_SESSION['user-email'])) {
            header('Location: ' . '/login');
            return;
        }

        header('Location: ' . '/todos');
    }

    public static function edit(App $app, array $params): ?string
    {
        if (!isset($_SESSION['user-email'])) {
            header('Location: ' . '/login');
            return null;
        }

        return $app->view->render(
            'todos/edit.twig',
            [
                'csrfToken' => $_SESSION['csrf-token'] ?? '',
                'userEmail' => $_SESSION['user-email'],
            ],
        );
    }

    public static function update(App $app, array $params)
    {
        if (!isset($_SESSION['user-email'])) {
            header('Location: ' . '/login');
            return;
        }

        header('Location: ' . '/todos');
    }

    public static function destroy(App $app, array $params)
    {
        if (!isset($_SESSION['user-email'])) {
            header('Location: ' . '/login');
            return;
        }

        header('Location: ' . '/todos');
    }
}

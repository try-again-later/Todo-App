<?php

declare(strict_types = 1);

namespace TryAgainLater\TodoApp\Controllers;

use RuntimeException;

use TryAgainLater\TodoApp\App;
use TryAgainLater\Pup\Attributes\{FromAssociativeArray, MakeParsed};
use TryAgainLater\Pup\Attributes\Generic\{AllowCoercions, ParsedProperty, Required};
use TryAgainLater\Pup\Attributes\String\{MinLength, MaxLength};
use TryAgainLater\TodoApp\Models\{User, Todo};

#[FromAssociativeArray]
class TodoData
{
    #[ParsedProperty, Required]
    #[MinLength(1), MaxLength(255)]
    public readonly string $title;

    #[ParsedProperty]
    public readonly ?string $body;

    use MakeParsed;
}

class TodoController
{
    public static function index(App $app): ?string
    {
        if (!isset($_SESSION['user-email'])) {
            header('Location: ' . '/login');
            return null;
        }

        $user = User::getByEmail($app->database->pdo(), $_SESSION['user-email']);
        if (!isset($user)) {
            throw new RuntimeException('Failed to fetch the user from database.');
        }

        $todos = Todo::getByUserId($app->database->pdo(), $user->id);

        return $app->view->render(
            'todos/index.twig',
            [
                'csrfToken' => $_SESSION['csrf-token'] ?? '',
                'userEmail' => $_SESSION['user-email'],
                'todos' => $todos,
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

        [$todoData, $errors] = TodoData::tryFrom($app->request->body);
        if (!empty($errors)) {
            return $app->view->render(
                'todos/create.twig',
                [
                    'csrfToken' => $_SESSION['csrf-token'] ?? '',
                    'userEmail' => $_SESSION['user-email'],
                ]
            );
        }

        $user = User::getByEmail($app->database->pdo(), $_SESSION['user-email']);
        if (!isset($user)) {
            throw new RuntimeException('Failed to fetch the user from database.');
        }

        $todo = new Todo(
            userId: $user->id,
            title: $todoData->title,
            body: empty($todoData->body ) ? null : $todoData->body,
        );
        $todo->save($app->database->pdo());

        header('Location: ' . '/todos');
    }

    public static function edit(App $app, array $params): ?string
    {
        if (!isset($_SESSION['user-email'])) {
            header('Location: ' . '/login');
            return null;
        }

        $user = User::getByEmail($app->database->pdo(), $_SESSION['user-email']);
        if (!isset($user)) {
            throw new RuntimeException('Failed to fetch the user from database.');
        }

        $todoId = intval($params['id']);
        $todo = Todo::getByTodoId($app->database->pdo(), $todoId);
        if (!isset($todo) || $todo->userId !== $user->id) {
            header('Location: ' . '/todos');
            return null;
        }

        return $app->view->render(
            'todos/edit.twig',
            [
                'csrfToken' => $_SESSION['csrf-token'] ?? '',
                'userEmail' => $_SESSION['user-email'],
                'todo' => $todo,
            ],
        );
    }

    public static function update(App $app, array $params)
    {
        if (!isset($_SESSION['user-email'])) {
            header('Location: ' . '/login');
            return;
        }

        $user = User::getByEmail($app->database->pdo(), $_SESSION['user-email']);
        if (!isset($user)) {
            throw new RuntimeException('Failed to fetch the user from database.');
        }

        $todoId = intval($params['id']);
        $todo = Todo::getByTodoId($app->database->pdo(), $todoId);
        if (!isset($todo) || $todo->userId !== $user->id) {
            header('Location: ' . '/todos');
            return null;
        }

        [$todoData, $errors] = TodoData::tryFrom($app->request->body);
        if (!empty($errors)) {
            header('Location: ' . "/todos/{$todo->id}/edit");
            return;
        }

        $todo->title = $todoData->title;
        $todo->body = $todoData->body;
        $todo->save($app->database->pdo());

        header('Location: ' . '/todos');
    }

    public static function destroy(App $app, array $params)
    {
        if (!isset($_SESSION['user-email'])) {
            header('Location: ' . '/login');
            return;
        }

        $user = User::getByEmail($app->database->pdo(), $_SESSION['user-email']);
        if (!isset($user)) {
            throw new RuntimeException('Failed to fetch the user from database.');
        }

        $todoId = intval($params['id']);
        $todo = Todo::getByTodoId($app->database->pdo(), $todoId);
        if (!isset($todo) || $todo->userId !== $user->id) {
            header('Location: ' . '/todos');
            return null;
        }

        $todo->delete($app->database->pdo());

        header('Location: ' . '/todos');
    }
}

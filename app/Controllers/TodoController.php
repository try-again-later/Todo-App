<?php

declare(strict_types = 1);

namespace TryAgainLater\TodoApp\Controllers;

use TryAgainLater\TodoApp\App;
use TryAgainLater\Pup\Attributes\{FromAssociativeArray, MakeParsed};
use TryAgainLater\Pup\Attributes\Generic\{ParsedProperty, AllowCoercions};
use TryAgainLater\Pup\Attributes\String\{MinLength, MaxLength};
use TryAgainLater\TodoApp\Models\{Todo};

#[FromAssociativeArray]
class TodoData
{
    #[ParsedProperty]
    #[MinLength(1), MaxLength(255)]
    public ?string $title = null;

    #[ParsedProperty]
    public ?string $body = null;

    #[ParsedProperty, AllowCoercions]
    public ?bool $completed = null;

    use MakeParsed;
}

class TodoController
{
    public static function index(App $app): ?string
    {
        $user = $app->user();
        $todos = Todo::getByUserId($app->database->pdo(), $user->id);

        $filter = $app->request->body['filter'] ?? 'all';
        if (!in_array($filter, ['finished', 'unfinished'], strict: true)) {
            $filter = 'all';
        }

        if ($filter === 'finished') {
            $todos = array_filter($todos, fn ($todo) => $todo->completed);
        }
        if ($filter === 'unfinished') {
            $todos = array_filter($todos, fn ($todo) => !$todo->completed);
        }

        return $app->view->render(
            'todos/index',
            [
                'todos' => $todos,
                'filter' => $filter,
                'redirect' => $filter === 'all' ? null : $app->request->uri,
            ],
        );
    }

    public static function create(App $app): ?string
    {
        return $app->view->render('todos/create');
    }

    public static function store(App $app)
    {
        [$todoData, $errors] = TodoData::tryFrom($app->request->body);
        if (!empty($errors)) {
            return $app->view->render('todos/create');
        }

        $user = $app->user();

        $todo = new Todo(
            userId: $user->id,
            title: $todoData->title,
            body: empty($todoData->body) ? null : $todoData->body,
            completed: false,
        );
        $todo->save($app->database->pdo());

        return $app->redirect('/todos');
    }

    public static function edit(App $app, array $params): ?string
    {
        $user = $app->user();

        $todoId = intval($params['id']);
        $todo = Todo::getByTodoId($app->database->pdo(), $todoId);
        if (!isset($todo) || $todo->userId !== $user->id) {
            return $app->redirect('/todos');
        }

        return $app->view->render(
            'todos/edit',
            [
                'todo' => $todo,
                'redirect' => $app->request->query['redirect'] ?? null,
            ],
        );
    }

    public static function update(App $app, array $params)
    {
        $user = $app->user();

        $todoId = intval($params['id']);
        $todo = Todo::getByTodoId($app->database->pdo(), $todoId);
        if (!isset($todo) || $todo->userId !== $user->id) {
            header('Location: ' . '/todos');
            return null;
        }

        [$todoData, $errors] = TodoData::tryFrom($app->request->body);
        if (!empty($errors)) {
            return $app->redirect("/todos/{$todo->id}/edit");
        }

        if (isset($todoData->title)) {
            $todo->title = $todoData->title;
        }
        if (isset($todoData->body)) {
            $todo->body = $todoData->body;
        }
        if (isset($todoData->completed)) {
            $todo->completed = $todoData->completed;
        }

        $todo->save($app->database->pdo());

        if (!isset($app->request->query['redirect'])) {
            return $app->redirect('/todos');
        }
        return $app->redirect($app->request->query['redirect']);
    }

    public static function destroy(App $app, array $params)
    {
        $user = $app->user();

        $todoId = intval($params['id']);
        $todo = Todo::getByTodoId($app->database->pdo(), $todoId);
        if (!isset($todo) || $todo->userId !== $user->id) {
            return $app->redirect('/todos');
        }

        $todo->delete($app->database->pdo());

        if (!isset($app->request->query['redirect'])) {
            return $app->redirect('/todos');
        }
        return $app->redirect($app->request->query['redirect']);
    }
}

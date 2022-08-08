<?php

declare(strict_types = 1);

namespace TryAgainLater\TodoApp\Models;

use PDO;
use RuntimeException;

class Todo
{
    public function __construct(
        public int $userId,
        public string $title,
        public bool $completed,
        public ?string $body = null,
        public ?int $id = null,
    )
    {
    }

    public static function getByUserId(PDO $pdo, int $userId): array
    {
        $statement = $pdo->prepare(<<<SQL
            SELECT todo_id, title, body, completed
            FROM todo
            WHERE user_id = :user_id
            SQL
        );
        $statement->bindValue(':user_id', $userId, PDO::PARAM_INT);

        if (!$statement->execute()) {
            return [];
        }

        $todos = [];
        foreach ($statement->fetchAll() as $todoData) {
            $todos[] = new Todo(
                id: $todoData['todo_id'],
                title: $todoData['title'],
                body: $todoData['body'] ?? null,
                userId: $userId,
                completed: $todoData['completed'],
            );
        }

        return $todos;
    }

    public static function getByTodoId(PDO $pdo, int $todoId): ?self
    {
        $statement = $pdo->prepare(<<<SQL
            SELECT title, body, user_id, completed
            FROM todo
            WHERE todo_id = :todo_id
            SQL
        );
        $statement->bindValue(':todo_id', $todoId, PDO::PARAM_INT);

        if (!$statement->execute()) {
            return null;
        }

        $todoData = $statement->fetch();
        if ($todoData === false) {
            return null;
        }

        return new Todo(
            title: $todoData['title'],
            body: $todoData['body'],
            userId: $todoData['user_id'],
            id: $todoId,
            completed: $todoData['completed'],
        );
    }

    public function save(PDO $pdo): bool
    {
        if (isset($this->id)) {
            $statement = $pdo->prepare(<<<SQL
                UPDATE todo
                SET
                    title = :title,
                    body = :body,
                    user_id = :user_id,
                    completed = :completed
                WHERE
                    todo_id = :todo_id
                SQL
            );
            $statement->bindValue(':todo_id', $this->id, PDO::PARAM_INT);
        } else {
            $statement = $pdo->prepare(<<<SQL
                INSERT INTO todo
                    (title, body, user_id, completed)
                VALUES
                    (:title, :body, :user_id, :completed)
                SQL
            );
        }
        $statement->bindValue(':title', $this->title, PDO::PARAM_STR);
        $statement->bindValue(':user_id', $this->userId, PDO::PARAM_INT);
        $statement->bindValue(':completed', $this->completed, PDO::PARAM_BOOL);

        if (isset($this->body)) {
            $statement->bindValue(':body', $this->body, PDO::PARAM_STR);
        } else {
            $statement->bindValue(':body', null, PDO::PARAM_NULL);
        }

        $executeResult = $statement->execute();
        if ($executeResult && !isset($this->id)) {
            $this->id = intval($pdo->lastInsertId());
        }
        return $executeResult;
    }

    public function delete(PDO $pdo): bool
    {
        if (!isset($this->id)) {
            throw new RuntimeException('Cannot delete a todo which has not been created yet');
        }

        $statement = $pdo->prepare(<<<SQL
            DELETE FROM todo
            WHERE todo_id = :todo_id
            SQL
        );
        $statement->bindValue(':todo_id', $this->id, PDO::PARAM_INT);

        return $statement->execute();
    }
}

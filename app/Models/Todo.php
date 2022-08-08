<?php

declare(strict_types = 1);

namespace TryAgainLater\TodoApp\Models;

use PDO;

class Todo
{
    public function __construct(
        public int $userId,
        public string $title,
        public ?string $body = null,
        public ?int $id = null,
    )
    {
    }

    public static function getByUserId(PDO $pdo, int $userId): array
    {
        $statement = $pdo->prepare(<<<SQL
            SELECT todo_id, title, body
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
            );
        }

        return $todos;
    }

    public function save(PDO $pdo): bool
    {
        if (isset($this->id)) {
            $statement = $pdo->prepare(<<<SQL
                UPDATE todo
                SET
                    title = :title,
                    body = :body,
                    user_id = :user_id
                WHERE
                    todo_id = :todo_id
                SQL
            );
            $statement->bindValue(':todo_id', $this->id, PDO::PARAM_INT);
        } else {
            $statement = $pdo->prepare(<<<SQL
                INSERT INTO todo
                    (title, body, user_id)
                VALUES
                    (:title, :body, :user_id)
                SQL
            );
        }
        $statement->bindValue(':title', $this->title, PDO::PARAM_STR);
        $statement->bindValue(':user_id', $this->userId, PDO::PARAM_INT);
        if (isset($this->body)) {
            $statement->bindValue(':body', $this->title, PDO::PARAM_STR);
        } else {
            $statement->bindValue(':body', null, PDO::PARAM_NULL);
        }

        $executeResult = $statement->execute();
        if ($executeResult) {
            $this->id = intval($pdo->lastInsertId());
        }
        return $executeResult;
    }
}

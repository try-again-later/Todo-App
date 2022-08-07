<?php

declare(strict_types = 1);

namespace TryAgainLater\TodoApp\Models;

use PDO;

class User
{
    public static function getByEmail(PDO $pdo, string $email): ?self
    {
        $statement = $pdo->prepare(<<<SQL
            SELECT user_id, email, password
            FROM "user"
            WHERE email = :email
            SQL
        );
        $statement->bindValue(':email', $email, PDO::PARAM_STR);

        if (!$statement->execute()) {
            return null;
        }

        $userData = $statement->fetch();
        if ($userData === false) {
            return null;
        }

        return new User(
            id: $userData['user_id'],
            email: $userData['email'],
            password: $userData['password'],
        );
    }

    public function save(PDO $pdo): bool
    {
        if (isset($this->id)) {
            $statement = $pdo->prepare(<<<SQL
                UPDATE "user"
                SET
                    email = :email,
                    password = :password
                WHERE
                    user_id = :id
                SQL
            );
            $statement->bindValue(':id', $this->id, PDO::PARAM_INT);
        } else {
            $statement = $pdo->prepare(<<<SQL
                INSERT INTO "user"
                    (email, password)
                VALUES
                    (:email, :password)
                SQL
            );
        }
        $statement->bindValue(':email', $this->email, PDO::PARAM_STR);
        $statement->bindValue(':password', $this->password, PDO::PARAM_STR);

        return $statement->execute();
    }

    public function __construct(
        public string $email,
        public string $password,
        public ?int $id = null,
    )
    {
    }
}

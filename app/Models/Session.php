<?php

declare (strict_types = 1);

namespace TryAgainLater\TodoApp\Models;

use PDO;
use RuntimeException;

class Session
{
    public const TOKEN_PATTERN = '/^(?<selector>[a-f0-9]{32}):(?<validator>[a-f0-9]{64})$/i';

    public static function getBySelector(PDO $pdo, string $selector): ?self
    {
        $statement = $pdo->prepare(<<<SQL
            SELECT session_id, validator, user_id, expiring_at
            FROM "session"
            WHERE
                selector = :selector AND
                expiring_at > NOW()
            SQL
        );
        $statement->bindValue(':selector', $selector);

        if (!$statement->execute()) {
            return null;
        }

        $sessionData = $statement->fetch();
        if ($sessionData === false) {
            return null;
        }

        return new self(
            id: $sessionData['session_id'],
            validator: $sessionData['validator'],
            userId: $sessionData['user_id'],
            expiringAt: $sessionData['expiring_at'],
            selector: $selector,
        );
    }

    public static function createSelectorValidatorToken(): array
    {
        $selector = bin2hex(random_bytes(16));
        $validator = bin2hex(random_bytes(32));

        $token = "$selector:$validator";

        return [$selector, $validator, $token];
    }

    public static function validateToken(?string $token): bool
    {
        if (!isset($token)) {
            return false;
        }
        return preg_match(self::TOKEN_PATTERN, $token) === 1;
    }

    public static function parseSelectorValidatorFromToken(string $token): array
    {
        if (!preg_match(self::TOKEN_PATTERN, $token, $matches)) {
            throw new RuntimeException('Invalid "remember me" token.');
        }

        return [$matches['selector'], $matches['validator']];
    }

    public function save(PDO $pdo): bool
    {
        $statement = $pdo->prepare(<<<SQL
            INSERT INTO "session"
                (validator, selector, expiring_at, user_id)
            VALUES
                (:validator, :selector, :expiring_at, :user_id)
            SQL
        );
        $statement->bindValue(':validator', $this->validator, PDO::PARAM_STR);
        $statement->bindValue(':selector', $this->selector, PDO::PARAM_STR);
        $statement->bindValue(':user_id', $this->userId, PDO::PARAM_INT);
        $statement->bindValue(':expiring_at', $this->expiringAt, PDO::PARAM_STR);

        $executeResult = $statement->execute();
        if ($executeResult) {
            $this->id = intval($pdo->lastInsertId());
        }
        return $executeResult;
    }

    public function delete(PDO $pdo): bool
    {
        if (!isset($this->id)) {
            throw new RuntimeException('Cannot delete a session which has not been created yet');
        }

        $statement = $pdo->prepare(<<<SQL
            DELETE FROM "session"
            WHERE session_id = :session_id
            SQL
        );
        $statement->bindValue(':session_id', $this->id, PDO::PARAM_INT);

        return $statement->execute();
    }

    public function user(PDO $pdo): User
    {
        $statement = $pdo->prepare(<<<SQL
            SELECT user_id, email, password
            FROM "user"
            WHERE "user".user_id = (
                SELECT "session".user_id
                FROM "session"
                WHERE
                    session_id = :session_id AND
                    expiring_at > NOW()
                LIMIT 1
            )
            SQL
        );
        $statement->bindValue(':session_id', $this->id);

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

    public function verify(string $validator): bool
    {
        return password_verify($validator, $this->validator);
    }

    public function __construct(
        public string $validator,
        public string $selector,
        public string $expiringAt,
        public int $userId,
        public ?int $id = null,
    )
    {
    }
}

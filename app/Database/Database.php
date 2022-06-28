<?php

declare(strict_types = 1);

namespace TryAgainLater\TodoApp\Database;

use PDO;

class Database
{
    private PDO $pdo;

    public function __construct(DatabaseConfig $config)
    {
        $this->pdo = new PDO(
            dsn: "{$config->driver()->value()}:host={$config->host()};dbname={$config->databaseName()}",
            username: $config->user(),
            password: $config->password(),
        );
    }

    public function pdo(): PDO
    {
        return $this->pdo;
    }
}

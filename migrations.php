<?php

require_once './app/bootstrap.php';

$resetTables = isset($argv[1]) && $argv[1] === 'reset';

if ($resetTables) {
    $database->pdo()->query(<<<SQL
        DROP TABLE IF EXISTS "user"
        SQL
    );
}

$database->pdo()->query(<<<SQL
    CREATE TABLE IF NOT EXISTS "user" (
        PRIMARY KEY (user_id),
        user_id SERIAL,
        email VARCHAR(255) UNIQUE,
        password CHAR(60)
    )
    SQL
);

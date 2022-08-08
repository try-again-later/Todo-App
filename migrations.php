<?php

require_once './app/bootstrap.php';

$resetTables = isset($argv[1]) && $argv[1] === 'reset';

// user

if ($resetTables) {
    $database->pdo()->query(<<<SQL
        DROP TABLE IF EXISTS "user" CASCADE
        SQL
    );
}

$database->pdo()->query(<<<SQL
    CREATE TABLE IF NOT EXISTS "user" (
        PRIMARY KEY (user_id),
        user_id SERIAL,
        email VARCHAR(255) NOT NULL UNIQUE,
        password CHAR(60) NOT NULL
    )
    SQL
);


// todo

if ($resetTables) {
    $database->pdo()->query(<<<SQL
        DROP TABLE IF EXISTS todo
        SQL
    );
}

$database->pdo()->query(<<<SQL
    CREATE TABLE IF NOT EXISTS todo (
        PRIMARY KEY (todo_id),
        todo_id SERIAL,
        user_id INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        body TEXT,

        FOREIGN KEY (user_id)
            REFERENCES "user" (user_id)
            ON DELETE CASCADE
            ON UPDATE CASCADE
    )
    SQL
);

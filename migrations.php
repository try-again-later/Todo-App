<?php

require_once './app/bootstrap.php';

$resetTables = isset($argv[1]) && $argv[1] === 'reset';

// users table

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


// "remember me" tokens table

if ($resetTables) {
    $database->pdo()->query(<<<SQL
        DROP TABLE IF EXISTS "session" CASCADE
        SQL
    );
}

$database->pdo()->query(<<<SQL
    CREATE TABLE IF NOT EXISTS "session" (
        PRIMARY KEY (session_id),
        session_id SERIAL,
        selector CHAR(32) NOT NULL,
        validator CHAR(60) NOT NULL,
        expiring_at TIMESTAMP NOT NULL,
        user_id INT NOT NULL,

        FOREIGN KEY (user_id)
            REFERENCES "user" (user_id)
            ON DELETE CASCADE
            ON UPDATE CASCADE
    )
    SQL
);


// todos table

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
        completed BOOLEAN NOT NULL DEFAULT FALSE,
        body TEXT,

        FOREIGN KEY (user_id)
            REFERENCES "user" (user_id)
            ON DELETE CASCADE
            ON UPDATE CASCADE
    )
    SQL
);

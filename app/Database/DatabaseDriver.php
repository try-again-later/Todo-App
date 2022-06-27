<?php

declare(strict_types = 1);

namespace TryAgainLater\TodoApp\Database;

use TryAgainLater\MultiBackedEnum\{MakeMultiBacked, MultiBackedEnum, Values};

#[MultiBackedEnum]
enum DatabaseDriver
{
    // Postgres database URL starts with "postgres://...", but PDO uses "pgsql" instead.
    // So use "pgsql", but allow parsing from "postgres".
    #[Values('pgsql', 'postgres')]
    case PostgreSQL;

    #[Values('mysql')]
    case MySQL;

    use MakeMultiBacked;
}

<?php

declare(strict_types = 1);

namespace TryAgainLater\TodoApp\Database;

use InvalidArgumentException;

class DatabaseConfig
{
    const DATABASE_URL_REGEXP = '/^(?<driver>.+?):\/\/(?<user>.+?):(?<password>.+)@(?<host>.+?):(?<port>\d+)\/(?<databaseName>.+)$/';

    public static function parseFromUrl(string $databaseUrl): static
    {
        if (!preg_match(static::DATABASE_URL_REGEXP, $databaseUrl, $matches)) {
            throw new InvalidArgumentException("Database URL ($databaseUrl) has invalid format.");
        }

        $driver = DatabaseDriver::tryFrom($matches['driver']);
        if (!isset($driver)) {
            $driverRawString = $matches['driver'];
            throw new InvalidArgumentException(
                "Database driver '$driverRawString' is not supported."
            );
        }

        return new static(
            driver: $driver,
            user: $matches['user'],
            password: $matches['password'],
            host: $matches['host'],
            port: intval($matches['port']),
            databaseName: ltrim($matches['databaseName'], '/'),
        );
    }

    public function __construct(
        private DatabaseDriver $driver,
        private string $user,
        private string $password,
        private string $host,
        private int $port,
        private string $databaseName,
    )
    {}

    public function driver(): DatabaseDriver
    {
        return $this->driver;
    }

    public function user(): string
    {
        return $this->user;
    }

    public function password(): string
    {
        return $this->password;
    }

    public function host(): string
    {
        return $this->host;
    }

    public function port(): int
    {
        return $this->port;
    }

    public function databaseName(): string
    {
        return $this->databaseName;
    }
}

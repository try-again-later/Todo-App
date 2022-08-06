<?php

declare(strict_types = 1);

namespace TryAgainLater\TodoApp\Environment;

use UnexpectedValueException;

class EnvironmentException extends UnexpectedValueException
{
    public static function becauseFailedToParse(string $variableName, string $value): self
    {
        return new self(
            "'{$variableName}' environment variable has invalid value of '{$value}'. "
        );
    }

    public static function bacauseVariableNotDefined(string $variableName): self
    {
        return new self("'{$variableName}' environment variable is not set.");
    }

    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}

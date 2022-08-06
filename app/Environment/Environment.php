<?php

declare(strict_types=1);

namespace TryAgainLater\TodoApp\Environment;

class Environment
{
    public const ENVIRONMENT_ARRAY_KEY = 'APP_ENV';

    public function __construct(private array $envArray)
    {
    }

    /**
     * Looks up the app environment inside the $_ENV array.
     * @throws UnknownEnvironmentException in case the corresponding environment variable is not
     *                                     set.
     */
    public function getType(): EnvironmentType
    {
        if (!isset($this->envArray[static::ENVIRONMENT_ARRAY_KEY])) {
            throw EnvironmentException::bacauseVariableNotDefined(
                static::ENVIRONMENT_ARRAY_KEY
            );
        }

        $rawValue = $this->envArray[static::ENVIRONMENT_ARRAY_KEY];
        $environment = EnvironmentType::tryFrom($rawValue);
        if (!isset($environment)) {
            throw EnvironmentException::becauseFailedToParse(
                static::ENVIRONMENT_ARRAY_KEY,
                $rawValue,
            );
        }

        return $environment;
    }

    /**
     * Checks if environment is set.
     */
    public function defined(): bool
    {
        return isset($this->envArray[static::ENVIRONMENT_ARRAY_KEY]) &&
            !empty(EnvironmentType::tryFrom($this->envArray[static::ENVIRONMENT_ARRAY_KEY]));
    }

    public function is(EnvironmentType ...$expectedEnvironments): bool
    {
        $currentEnvironment = $this->getType();
        return in_array($currentEnvironment, $expectedEnvironments, strict: true);
    }
}

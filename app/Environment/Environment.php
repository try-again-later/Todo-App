<?php

declare (strict_types = 1);

namespace TryAgainLater\TodoApp\Environment;

enum Environment: string
{
    case Development = 'development';
    case Local = 'local';
    case Staging = 'staging';
    case Production = 'production';

    public const ENVIRONMENT_ARRAY_KEY = 'APP_ENV';

    /**
     * Looks up the app environment inside the $_ENV array.
     * @throws UnknownEnvironmentException in case the corresponding environment variable is not
     *                                     set.
     */
    public static function get(): Environment
    {
        if (!isset($_ENV[static::ENVIRONMENT_ARRAY_KEY])) {
            throw UnknownEnvironmentException::bacauseVariableNotDefined(
                static::ENVIRONMENT_ARRAY_KEY
            );
        }

        $rawValue = $_ENV[static::ENVIRONMENT_ARRAY_KEY];
        $environment = Environment::tryFrom($rawValue);
        if (!isset($environment)) {
            throw UnknownEnvironmentException::becauseFailedToParse(
                static::ENVIRONMENT_ARRAY_KEY,
                $rawValue,
            );
        }

        return $environment;
    }

    /**
     * Checks if environment is set.
     */
    public static function defined(): bool
    {
        return isset($_ENV[static::ENVIRONMENT_ARRAY_KEY]) &&
            !empty(Environment::tryFrom($_ENV[static::ENVIRONMENT_ARRAY_KEY]));
    }

    public static function is(Environment ...$expectedEnvironments): bool
    {
        $currentEnvironment = static::get();
        return in_array($currentEnvironment, $expectedEnvironments, strict: true);
    }
}

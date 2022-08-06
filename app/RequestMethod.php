<?php

declare(strict_types=1);

namespace TryAgainLater\TodoApp;

use ValueError;

enum RequestMethod
{
    case GET;
    case POST;

    public function value(): string
    {
        return match ($this) {
            self::GET => 'GET',
            self::POST => 'POST',
            default => 'GET',
        };
    }

    // backed enums are case-sensitive unfortunately
    public static function fromString(string $string): self
    {
        if (!in_array(strtolower($string), ['get', 'post'], strict: true)) {
            throw new ValueError("Failed to convert the string \"$string\" to a request method.");
        }

        return match (strtolower($string)) {
            'get' => self::GET,
            'post' => self::POST,
        };
    }
}

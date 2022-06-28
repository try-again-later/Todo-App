<?php

declare(strict_types = 1);

namespace TryAgainLater\TodoApp\Util;

class Path
{
    static function joinSegments(string ...$segments): string
    {
        return implode(DIRECTORY_SEPARATOR, $segments);
    }

    static function appendFile(string $folderPath, string ...$segments): string
    {
        assert(str_ends_with($folderPath, DIRECTORY_SEPARATOR));

        return $folderPath . self::joinSegments(...$segments);
    }

    static function appendFolder(string $folderPath, string ...$segments): string
    {
        assert(str_ends_with($folderPath, DIRECTORY_SEPARATOR));

        if (count($segments) === 0) {
            return $folderPath;
        }

        return self::appendFile($folderPath, ...$segments) . DIRECTORY_SEPARATOR;
    }
}

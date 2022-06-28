<?php

declare (strict_types = 1);

namespace TryAgainLater\TodoApp;

use TryAgainLater\TodoApp\Util\Path;

class AppPaths
{
    public function __construct(private string $rootPath)
    {
        assert(str_ends_with($rootPath, DIRECTORY_SEPARATOR));
    }

    public function root(string ...$segments): string
    {
        return Path::appendFile($this->rootPath, ...$segments);
    }

    public function storage(string ...$segments): string
    {
        return $this->root('storage', ...$segments);
    }

    public function public(string ...$segments): string
    {
        return $this->root('public', ...$segments);
    }

    public function logs(string ...$segments): string
    {
        return Path::appendFile(
            Path::appendFolder($this->rootPath, 'storage', 'logs'),
            ...$segments,
        );
    }

    public function errorLog(): string
    {
        return $this->logs('error.log');
    }
}

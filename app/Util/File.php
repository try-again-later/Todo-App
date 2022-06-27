<?php

declare(strict_types = 1);

namespace TryAgainLater\TodoApp\Util;

enum FileCreationResult
{
    case Success;
    case AlreadyExists;
    case Failure;

    public function then(callable $onSuccess)
    {
        if ($this === self::Success) {
            $onSuccess($this);
        }
        return $this;
    }

    public function catch(callable $onFailure)
    {
        if ($this === self::Failure) {
            $onFailure($this);
        }
        return $this;
    }

    public function finally(callable $inAnyCase)
    {
        $inAnyCase($this);
        return $this;
    }
}

class File
{
    public static function create(string $fileName): FileCreationResult
    {
        if (file_exists($fileName)) {
            return FileCreationResult::AlreadyExists;
        }

        $parentDirectory = dirname($fileName);

        if (!file_exists($parentDirectory)) {
            $parentDirectoryCreated = mkdir($parentDirectory, recursive: true);
            if (!$parentDirectoryCreated) {
                return FileCreationResult::Failure;
            }
        }

        $fileHandle = fopen($fileName, 'w');
        if ($fileHandle === false) {
            return FileCreationResult::Failure;
        }

        $fileCloseResult = fclose($fileHandle);
        if (!$fileCloseResult) {
            return FileCreationResult::Failure;
        }

        return FileCreationResult::Success;
    }
}

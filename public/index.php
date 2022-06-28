<?php

declare(strict_types = 1);

namespace TryAgainLater\TodoApp;

use Throwable;

use TryAgainLater\TodoApp\Environment\Environment;
use TryAgainLater\TodoApp\Util\File;

define('ROOT_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);

require_once ROOT_PATH . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

if (!Environment::defined()) {
    echo 'Environment is not defined.';
    http_response_code(500);
    return;
}

$appPaths = new AppPaths(ROOT_PATH);


// Handle logging uncaught exceptions depending on current environment

if (Environment::is(Environment::Staging, Environment::Production)) {
    ini_set('display_errors', false);
}

$logFileCreation = File::create($appPaths->errorLog());
if ($logFileCreation->failed()) {
    echo 'Permissions are not set correctly.';
    http_response_code(500);
    return;
}

set_exception_handler(function (Throwable $error) use ($appPaths) {
    $message =
        "Error: '{$error->getMessage()}' in '{$error->getFile()}' on line {$error->getLine()}.";

    if (!Environment::is(Environment::Staging, Environment::Production)) {
        echo $message;
    }

    if (file_exists($appPaths->errorLog())) {
        error_log($message . PHP_EOL, message_type: 3, destination: $appPaths->errorLog());
    }
});

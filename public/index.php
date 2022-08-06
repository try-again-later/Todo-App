<?php

declare(strict_types = 1);

namespace TryAgainLater\TodoApp;

use Throwable;

use Dotenv\Dotenv;
use Twig\Loader\FilesystemLoader as TwigLoader;
use Twig\Environment as TwigEnvironment;

use TryAgainLater\TodoApp\Database\{Database, DatabaseConfig};
use TryAgainLater\TodoApp\Environment\Environment;
use TryAgainLater\TodoApp\Util\File;

const REQUIRED_ENV_VARS = ['APP_ENV', 'MEMCACHED_SERVERS'];

if (count(array_intersect_key(array_flip(REQUIRED_ENV_VARS), $_ENV)) !== count(REQUIRED_ENV_VARS)) {
    echo 'Environment variables "' . implode(', ', REQUIRED_ENV_VARS) . '" are not defined.';
    http_response_code(500);
    return;
}

ini_set('session.save_handler', 'memcached');
ini_set('session.save_path', $_ENV['MEMCACHED_SERVERS']);
ini_set('memcached.sess_persistent', 1);
ini_set('memcached.sess_binary_protocol', 1);

if (!empty($_ENV['MEMCACHED_USERNAME']) && !empty($_ENV['MEMCACHED_PASSWORD'])) {
    ini_set('memcached.sess_sasl_username', $_ENV['MEMCACHED_USERNAME']);
    ini_set('memcached.sess_sasl_password', $_ENV['MEMCACHED_PASSWORD']);
}

session_start();

define('ROOT_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);

require_once ROOT_PATH . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

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


// Load configs from .env file

$rawConfigs = [];
if (file_exists($appPaths->env())) {
    $rawConfigs = Dotenv::createArrayBacked(
        $appPaths->root(),
        basename($appPaths->env()),
    )->load();
}
$rawConfigs = array_merge($rawConfigs, $_ENV);

$databaseConfig = DatabaseConfig::parseFromArray($rawConfigs);
$database = new Database($databaseConfig);

$twigLoader = new TwigLoader($appPaths->templates());
$twig = new TwigEnvironment($twigLoader, []);
$template = $twig->load('index.twig');


$databaseTest = $database->pdo()->query("SELECT 'database is ok' as test")->fetch()['test'];
if (!isset($_SESSION['test'])) {
    $_SESSION['test'] = 0;
} else {
    ++$_SESSION['test'];
}
echo $template->render(['counter' => $_SESSION['test'], 'database' => $databaseTest]);

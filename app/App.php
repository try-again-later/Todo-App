<?php

declare(strict_types = 1);

namespace TryAgainLater\TodoApp;

use TryAgainLater\TodoApp\Database\Database;
use Twig\Environment as TwigEnvironment;

class App
{
    public function __construct(
        public readonly Database $database,
        public readonly TwigEnvironment $view,
        public readonly Request $request,
    )
    {}
}

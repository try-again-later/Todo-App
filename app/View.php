<?php

declare(strict_types = 1);

namespace TryAgainLater\TodoApp;

use Twig\Environment as TwigEnvironment;

class View
{
    public function __construct(
        private App $app,
        private TwigEnvironment $twig,
    )
    {
    }

    public function render(string $viewName, array $params = []): string
    {
        if (!str_ends_with($viewName, '.twig')) {
            $viewName = $viewName . '.twig';
        }
        if (!isset($params['csrfToken'])) {
            $params['csrfToken'] = $this->app->csrfToken();
        }
        if (!isset($params['userEmail']) && isset($_SESSION['user-email'])) {
            if ($this->app->auth()) {
                $params['userEmail'] = $_SESSION['user-email'];
            }
        }

        return $this->twig->render($viewName, $params);
    }
}

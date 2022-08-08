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

        if ($this->app->auth()) {
            $params['userEmail'] = $_SESSION['user-email'];
        }
        $params['csrfToken'] ??= $this->app->csrfToken();
        $params['uri'] ??= $this->app->request->uri;

        return $this->twig->render($viewName, $params);
    }
}

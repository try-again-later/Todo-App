<?php

declare(strict_types = 1);

namespace TryAgainLater\TodoApp;

use Twig\Environment as TwigEnvironment;

use TryAgainLater\TodoApp\Database\Database;
use TryAgainLater\TodoApp\Models\User;

class App
{
    public View $view;

    public function __construct(
        TwigEnvironment $twig,
        public readonly Database $database,
        public readonly Request $request,
    )
    {
        $this->view = new View($this, $twig);
    }

    public function csrfToken(): ?string
    {
        return $_SESSION['csrf-token'] ?? null;
    }

    public function auth(): bool
    {
        return isset($_SESSION['user-email']) && !empty($this->user());
    }

    public function user(): ?User
    {
        if (!isset($_SESSION['user-email'])) {
            return null;
        }

        $user = User::getByEmail($this->database->pdo(), $_SESSION['user-email']);
        if (!isset($user)) {
            unset($_SESSION['user-email']);
            return null;
        }

        return $user;
    }

    public function redirect(string $to)
    {
        header('Location: ' . $to);
        return null;
    }
}

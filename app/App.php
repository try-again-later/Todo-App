<?php

declare(strict_types = 1);

namespace TryAgainLater\TodoApp;

use RuntimeException;

use TryAgainLater\TodoApp\Controllers\SessionController;
use Twig\Environment as TwigEnvironment;

use TryAgainLater\TodoApp\Database\Database;
use TryAgainLater\TodoApp\Models\{User, Session};

class App
{
    public const FLASH_MESSAGES_KEY = 'flash-messages';

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
        return !empty($this->user());
    }

    public function user(): ?User
    {
        if (isset($_SESSION['user-email'])) {
            $user = User::getByEmail($this->database->pdo(), $_SESSION['user-email']);
            if (!isset($user)) {
                unset($_SESSION['user-email']);
                return null;
            }

            return $user;
        }

        if (!isset($_COOKIE[SessionController::REMEMBER_ME_COOKIE_NAME])) {
            return null;
        }

        $token = $_COOKIE[SessionController::REMEMBER_ME_COOKIE_NAME];
        if (!Session::validateToken($token)) {
            setcookie(SessionController::REMEMBER_ME_COOKIE_NAME, '', -1);
            return null;
        }
        [$selector, $validator] = Session::parseSelectorValidatorFromToken($token);
        $session = Session::getBySelector($this->database->pdo(), $selector);

        if (!isset($session) || !$session->verify($validator)) {
            setcookie(SessionController::REMEMBER_ME_COOKIE_NAME, '', -1);
            return null;
        }

        $user = $session->user($this->database->pdo());
        if (isset($user)) {
            $_SESSION['user-email'] = $user->email;
        }
        return $user;
    }

    public function redirect(string $to)
    {
        header('Location: ' . $to);
        return null;
    }

    public function setFlashMessage(string $key, string $value): void
    {
        $_SESSION[self::FLASH_MESSAGES_KEY][$key] = $value;
    }

    public function getFlashMessage(string $key): ?string
    {
        if (
            !isset($_SESSION[self::FLASH_MESSAGES_KEY]) ||
            !isset($_SESSION[self::FLASH_MESSAGES_KEY][$key])
        ) {
            return null;
        }

        $value = $_SESSION[self::FLASH_MESSAGES_KEY][$key];
        unset($_SESSION[self::FLASH_MESSAGES_KEY][$key]);
        return $value;
    }
}

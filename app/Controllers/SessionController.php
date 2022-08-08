<?php

declare(strict_types = 1);

namespace TryAgainLater\TodoApp\Controllers;

use TryAgainLater\Pup\Attributes\{FromAssociativeArray, MakeParsed};
use TryAgainLater\Pup\Attributes\Generic\{ParsedProperty, Required};
use TryAgainLater\Pup\Attributes\String\{MinLength, MaxLength};
use TryAgainLater\TodoApp\App;
use TryAgainLater\TodoApp\Models\User;

#[FromAssociativeArray]
class LoginData
{
    #[ParsedProperty, Required]
    #[MinLength(1), MaxLength(255)]
    public readonly string $email;

    #[ParsedProperty, Required]
    #[MinLength(1)]
    public readonly string $password;

    use MakeParsed;
}

class SessionController
{
    public static function create(App $app): string
    {
        return $app->view->render(
            'session/create.twig',
            ['csrfToken' => $_SESSION['csrf-token'] ?? ''],
        );
    }

    public static function store(App $app)
    {
        [$loginData, $errors] = LoginData::tryFrom($app->request->body);

        $keysToErrors = [];
        foreach ($errors as [$key, $error]) {
            $keysToErrors[$key][] = $error;
        }

        $userModel = null;
        if (isset($loginData)) {
            $userModel = User::getByEmail($app->database->pdo(), $loginData->email);

            if (!isset($userModel)) {
                $keysToErrors['email'][] = 'Invalid email or password.';
            }
        }
        if (isset($userModel)) {
            if (password_verify($loginData->password, $userModel->password)) {
                $_SESSION['user-email'] = $loginData->email;
                return header('Location: ' . '/');
            } else {
                $keysToErrors['email'][] = 'Invalid email or password.';
            }
        }

        return $app->view->render(
            'session/create.twig',
            [
                'csrfToken' => $_SESSION['csrf-token'] ?? '',
                'errors' => $keysToErrors,
            ],
        );
    }

    public static function destroy()
    {
        session_unset();
        session_destroy();
        header('Location: ' . '/');
    }
}

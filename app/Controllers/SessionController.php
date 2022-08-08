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
    public static function create(App $app): ?string
    {
        if ($app->auth()) {
            return $app->redirect('/');
        }

        return $app->view->render('session/create');
    }

    public static function store(App $app)
    {
        if ($app->auth()) {
            return $app->redirect('/');
        }

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
                return $app->redirect('/');
            } else {
                $keysToErrors['email'][] = 'Invalid email or password.';
            }
        }

        return $app->view->render(
            'session/create',
            ['errors' => $keysToErrors],
        );
    }

    public static function destroy(App $app)
    {
        if ($app->auth()) {
            session_unset();
            session_destroy();
        }
        return $app->redirect('/');
    }
}

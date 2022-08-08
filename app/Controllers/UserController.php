<?php

declare(strict_types = 1);

namespace TryAgainLater\TodoApp\Controllers;

use TryAgainLater\Pup\Attributes\{FromAssociativeArray, MakeParsed};
use TryAgainLater\Pup\Attributes\Generic\{ParsedProperty, Required, Test};
use TryAgainLater\Pup\Attributes\String\{MinLength, MaxLength};
use TryAgainLater\TodoApp\App;
use TryAgainLater\TodoApp\Models\User;

#[FromAssociativeArray]
class SignUpData
{
    public static function checkEmail(string $string): bool
    {
        return filter_var($string, FILTER_VALIDATE_EMAIL) !== false;
    }

    #[ParsedProperty, Required]
    #[MinLength(1), MaxLength(255)]
    #[Test(
        name: 'Is email',
        check: [self::class, 'checkEmail'],
        message: 'Is not an email.',
    )]
    public readonly string $email;

    #[ParsedProperty, Required]
    #[MinLength(6)]
    public readonly string $password;

    #[ParsedProperty('repeated-password'), Required]
    #[MinLength(6)]
    public readonly string $repeatedPassword;

    use MakeParsed;
}

class UserController
{
    public static function create(App $app): ?string
    {
        if ($app->auth()) {
            return $app->redirect('/');
        }

        return $app->view->render('user/create');
    }

    public static function store(App $app)
    {
        if ($app->auth()) {
            return $app->redirect('/');
        }

        [$signUpData, $errors] = SignUpData::tryFrom($app->request->body);

        $_SESSION['last-user-input'] = $app->request->body;

        $keysToErrors = [];
        foreach ($errors as [$key, $error]) {
            $keysToErrors[$key][] = $error;
        }
        if ($app->request->body['repeated-password'] != $app->request->body['password']) {
            $keysToErrors['repeated-password'][] = 'Passwords do not match.';
        }

        if (
            isset($signUpData) &&
            !is_null(User::getByEmail($app->database->pdo(), $signUpData->email))
        ) {
            $keysToErrors['email'][] = 'This email is already registered.';
        }

        if (!empty($keysToErrors)) {
            return $app->view->render(
                'user/create',
                [
                    'errors' => $keysToErrors,
                    'values' => $_SESSION['last-user-input'] ?? [],
                ],
            );
        }

        $hashedPassword = password_hash($signUpData->password, PASSWORD_BCRYPT);
        $user = new User(
            email: $signUpData->email,
            password: $hashedPassword,
        );
        $user->save($app->database->pdo());

        $_SESSION['user-email'] = $user->email;
        unset($_SESSION['last-user-input']);

        return $app->redirect('/');
    }
}

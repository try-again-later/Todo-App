<?php

declare(strict_types = 1);

namespace TryAgainLater\TodoApp\Controllers;

use TryAgainLater\Pup\Attributes\{FromAssociativeArray, MakeParsed};
use TryAgainLater\Pup\Attributes\Generic\{ParsedProperty, Required, Test};
use TryAgainLater\Pup\Attributes\String\MinLength;
use TryAgainLater\TodoApp\App;

#[FromAssociativeArray]
class SignUpData
{
    public static function checkEmail(string $string): bool
    {
        return filter_var($string, FILTER_VALIDATE_EMAIL) !== false;
    }

    #[ParsedProperty, Required]
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
    public static function create(App $app): string
    {
        return $app->view->render('user/create.twig');
    }

    public static function store(App $app)
    {
        [$signUpData, $errors] = SignUpData::tryFrom($app->request->body);

        $keysToErrors = [];
        foreach ($errors as [$key, $error]) {
            $keysToErrors[$key][] = $error;
        }
        if ($app->request->body['repeated-password'] != $app->request->body['password']) {
            $keysToErrors['repeated-password'][] = 'Passwords do not match.';
        }

        if (!empty($errors)) {
            return $app->view->render('user/create.twig', ['errors' => $keysToErrors]);
        }

        header('Location: ' . '/');
    }
}

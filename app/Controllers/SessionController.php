<?php

declare(strict_types = 1);

namespace TryAgainLater\TodoApp\Controllers;

use RuntimeException;

use TryAgainLater\Pup\Attributes\{FromAssociativeArray, MakeParsed};
use TryAgainLater\Pup\Attributes\Generic\{ParsedProperty, Required};
use TryAgainLater\Pup\Attributes\String\{MinLength, MaxLength};
use TryAgainLater\TodoApp\App;
use TryAgainLater\TodoApp\Models\{Session, User};

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
    public const REMEMBER_ME_COOKIE_NAME = 'remember_me';

    public const DAYS_SESSION_LIFESPAN = 30;

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

        $_SESSION['last-user-input'] = $app->request->body;

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

        if (isset($userModel) && !password_verify($loginData->password, $userModel->password)) {
            $keysToErrors['email'][] = 'Invalid email or password.';
        }

        if (!empty($keysToErrors)) {
            return $app->view->render(
                'session/create',
                [
                    'errors' => $keysToErrors,
                    'values' => $_SESSION['last-user-input'],
                ],
            );
        }

        if ($app->request->body['remember-me']) {
            $user = User::getByEmail($app->database->pdo(), $loginData->email);

            [$selector, $validator, $token] = Session::createSelectorValidatorToken();

            $expiringAt = time() + 60 * 60 * 24 * self::DAYS_SESSION_LIFESPAN;
            $expiringAtString = date('Y-m-d H:i:s', $expiringAt);

            $hashedValidator = password_hash($validator, PASSWORD_BCRYPT);

            $session = new Session(
                validator: $hashedValidator,
                selector: $selector,
                expiringAt: $expiringAtString,
                userId: $user->id,
            );

            $sessionSaveResult = $session->save($app->database->pdo());
            if (!$sessionSaveResult) {
                throw new RuntimeException('Internal error');
            }

            setcookie(self::REMEMBER_ME_COOKIE_NAME, $token, $expiringAt);
        }

        $_SESSION['user-email'] = $loginData->email;
        unset($_SESSION['last-user-input']);
        return $app->redirect('/');
    }

    public static function destroy(App $app)
    {
        if (!$app->auth()) {
            return $app->redirect('/');
        }

        if (isset($_COOKIE[self::REMEMBER_ME_COOKIE_NAME])) {
            [$selector, $validator] = Session::parseSelectorValidatorFromToken(
                $_COOKIE[self::REMEMBER_ME_COOKIE_NAME]
            );
            $session = Session::getBySelector($app->database->pdo(), $selector);
            setcookie(self::REMEMBER_ME_COOKIE_NAME, '', -1);

            if (isset($session) && $session->verify($validator)) {
                $session->delete($app->database->pdo());
            }
        }

        session_unset();
        session_destroy();

        return $app->redirect('/');
    }
}

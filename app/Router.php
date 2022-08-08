<?php

declare(strict_types = 1);

namespace TryAgainLater\TodoApp;

class Route
{
    public function __construct(
        public $callback,
        public bool $auth = false,
    )
    {
    }
}

class Router
{
    private array $routes = [];
    private $onRouteNotFound = null;

    public function __construct(private Request $request)
    {
    }

    public function register(
        RequestMethod $requestMethod,
        string $route,
        callable $controller,
        bool $auth = false,
    ): self
    {
        if (!str_starts_with($route, '/')) {
            $route = '/' . $route;
        }
        if (!str_ends_with($route, '/')) {
            $route = $route . '/?';
        }

        $this->routes[$requestMethod->value()]["<^$route\$>"] = new Route($controller, $auth);
        return $this;
    }

    public function get(string $route, callable $controller, bool $auth = false): self
    {
        return $this->register(RequestMethod::GET, $route, $controller, $auth);
    }

    public function post(string $route, callable $controller, bool $auth = false): self
    {
        return $this->register(RequestMethod::POST, $route, $controller, $auth);
    }

    public function onNotFound(callable $controller): self
    {
        $this->onRouteNotFound = new Route($controller);
        return $this;
    }

    public function resolve(App $app): ?string
    {
        $methodAsString = $this->request->method->value();
        $requestPath = $this->request->path;

        if (isset($this->routes[$methodAsString])) {
            foreach ($this->routes[$methodAsString] as $routePath => $route) {
                if (preg_match($routePath, $requestPath, $matches) === 1) {
                    if ($route->auth && !$app->auth()) {
                        return $app->redirect('/login');
                    }

                    return ($route->callback)($app, $matches);
                }
            }
        }

        if (!isset($this->onRouteNotFound)) {
            return null;
        }
        return ($this->onRouteNotFound->callback)($app);
    }
}

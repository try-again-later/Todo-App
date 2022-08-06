<?php

declare(strict_types = 1);

namespace TryAgainLater\TodoApp;

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
    ): self
    {
        if (!str_starts_with($route, '/')) {
            $route = '/' . $route;
        }
        $this->routes[$requestMethod->value()][$route] = $controller;
        return $this;
    }

    public function get(string $route, callable $controller): self
    {
        return $this->register(RequestMethod::GET, $route, $controller);
    }

    public function post(string $route, callable $controller): self
    {
        return $this->register(RequestMethod::POST, $route, $controller);
    }

    public function onNotFound(callable $controller): self
    {
        $this->onRouteNotFound = $controller;
        return $this;
    }

    public function resolve(App $app): ?string
    {
        $methodAsString = $this->request->method->value();
        $requestPath = $this->request->path;

        if (
            isset($this->routes[$methodAsString]) &&
            isset($this->routes[$methodAsString][$requestPath])
        ) {
            return ($this->routes[$methodAsString][$requestPath])($app);
        }

        if (!isset($this->onRouteNotFound)) {
            return null;
        }
        return ($this->onRouteNotFound)($app);
    }
}

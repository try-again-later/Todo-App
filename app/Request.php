<?php

declare(strict_types = 1);

namespace TryAgainLater\TodoApp;

use InvalidArgumentException;

use TryAgainLater\Pup\Schema;


class Request
{
    public readonly string $path;
    public readonly string $uri;
    public readonly RequestMethod $method;
    public readonly array $body;

    public function __construct(
        private array $serverArray,
        private array $getArray,
        private array $postArray,
    )
    {
        [$_, $serverArrayErrors] = Schema::associativeArray([
            'REQUEST_URI' => Schema::string()->required(),
            'REQUEST_METHOD' => Schema::string()->required()->oneOf('GET', 'POST'),
        ])->validate($serverArray)->tryGet();

        if (!empty($serverArrayErrors)) {
            throw new InvalidArgumentException('Invalid "server" array.');
        }

        $this->uri = $serverArray['REQUEST_URI'];
        $this->path = explode('?', $this->uri)[0];
        $this->method = RequestMethod::fromString($serverArray['REQUEST_METHOD']);

        if ($this->method === RequestMethod::GET) {
            $this->body = $getArray;
        } else if ($this->method === RequestMethod::POST) {
            $this->body = $postArray;
        }
    }
}

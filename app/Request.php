<?php

declare(strict_types = 1);

namespace TryAgainLater\TodoApp;

use InvalidArgumentException;
use RuntimeException;
use TryAgainLater\Pup\Schema;


class Request
{
    public readonly string $path;
    public readonly array $query;
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

        $queryArray = [];
        $query = parse_url($serverArray['REQUEST_URI'], PHP_URL_QUERY);
        if (isset($query)) {
            parse_str($query, $queryArray);
        }
        $this->query = $queryArray;

        $this->method = RequestMethod::fromString($serverArray['REQUEST_METHOD']);

        if (!isset($_SESSION['csrf-token'])) {
            $_SESSION['csrf-token'] = bin2hex(random_bytes(35));
        }

        if ($this->method === RequestMethod::GET) {
            $this->body = $getArray;
        } else if ($this->method === RequestMethod::POST) {
            if (
                !isset($postArray['csrf-token']) ||
                $postArray['csrf-token'] !== $_SESSION['csrf-token']
            )
            {
                http_response_code(403);
                throw new RuntimeException('CSRF token mismatch.');
            }
            $this->body = $postArray;
        }
    }
}

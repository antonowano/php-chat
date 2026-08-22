<?php

namespace Antonowano\Chat\Stubs;

use Antonowano\Chat\Api\HttpRequest;

readonly class StubHttpRequest implements HttpRequest
{
    public function __construct(
        private string $method = 'GET',
        private string $path = '/',
        private array $queryString = [],
        private array $content = [],
        private array $headers = [],
    ) {
    }

    public function path(): string
    {
        return $this->path;
    }

    public function httpMethod(): string
    {
        return $this->method;
    }

    public function content(): string
    {
        return json_encode($this->content);
    }

    public function queryString(): string
    {
        return http_build_query($this->queryString);
    }

    public function header(string $name): ?string
    {
        return $this->headers[$name] ?? null;
    }
}

<?php

namespace Antonowano\Chat;

use Closure;

readonly class ApiRoute
{
    private Closure $callback;

    public function __construct(
        private string $method,
        private string $path,
        callable $callback,
    ) {
        $this->callback = $callback(...);
    }

    public function method(): string
    {
        return $this->method;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function callback(): callable
    {
        return $this->callback;
    }
}
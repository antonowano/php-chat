<?php

namespace Antonowano\Chat\Swoole;

readonly class ApiRequest
{
    public function __construct(
        private object $swooleRequest,
    ) {
    }

    public function isPath(string $pattern): bool
    {
        return $this->swooleRequest->server['path_info'] === $pattern;
    }

    public function isMethod(string $method): bool
    {
        return $this->swooleRequest->getMethod() === $method;
    }

    public function json(): Json
    {
        return Json::create($this->swooleRequest->getContent());
    }
}

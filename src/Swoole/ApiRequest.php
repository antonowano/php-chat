<?php

namespace Antonowano\Chat\Swoole;

use OpenSwoole\Http\Request;

readonly class ApiRequest
{
    public function __construct(
        private Request $swooleRequest,
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

    public function json(): DataBag
    {
        return DataBag::fromJson($this->swooleRequest->getContent());
    }

    public function query(): DataBag
    {
        return DataBag::fromQuery($this->swooleRequest->server['query_string']);
    }
}

<?php

namespace Antonowano\Chat\Swoole;

use Antonowano\Chat\HttpRequest;
use OpenSwoole\Http\Request;

readonly class SwooleHttpRequest implements HttpRequest
{
    public function __construct(
        private Request $swooleRequest,
    ) {
    }

    public function path(): string
    {
        return $this->swooleRequest->server['path_info'];
    }

    public function httpMethod(): string
    {
        return $this->swooleRequest->getMethod();
    }

    public function content(): string
    {
        return $this->swooleRequest->getContent();
    }

    public function queryString(): string
    {
        return $this->swooleRequest->server['query_string'];
    }
}

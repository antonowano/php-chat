<?php

namespace Antonowano\Chat\Swoole;

use OpenSwoole\Http\Response;

readonly class ApiResponse
{
    public function __construct(
        private Response $swooleResponse,
    ) {
    }

    public function json(array $data, int $status = 200): void
    {
        $this->swooleResponse->header('Content-Type', 'application/json');
        $this->swooleResponse->status($status);
        $this->swooleResponse->end(json_encode($data));
    }
}

<?php

namespace Antonowano\Chat\Swoole;

use Antonowano\Chat\Stream\WsResponse;
use OpenSwoole\WebSocket\Server;

readonly class SwooleWsResponse implements WsResponse
{
    public function __construct(
        private Server $server,
        private int $fd,
    ) {
    }

    public function push(array $data): void
    {
        $this->server->push($this->fd, json_encode($data));
    }
}

<?php

namespace Antonowano\Chat\Swoole;

use Antonowano\Chat\Stream\WsFrame;
use OpenSwoole\WebSocket\Frame;

readonly class SwooleWsFrame implements WsFrame
{
    public function __construct(
        private Frame $swooleFrame,
    ) {
    }

    public function data(): string
    {
        return $this->swooleFrame->data;
    }
}

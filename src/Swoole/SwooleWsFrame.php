<?php

namespace Antonowano\Chat\Swoole;

use Antonowano\Chat\Stream\RawFrame;
use OpenSwoole\WebSocket\Frame;

readonly class SwooleWsFrame implements RawFrame
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

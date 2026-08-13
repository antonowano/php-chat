<?php

namespace Antonowano\Chat\Swoole;

use OpenSwoole\WebSocket\Frame;

readonly class WsFrame
{
    public function __construct(
        private Frame $swooleFrame,
    ) {
    }

    public function data(): DataBag
    {
        return DataBag::fromJson($this->swooleFrame->data);
    }
}

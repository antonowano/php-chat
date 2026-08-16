<?php

namespace Antonowano\Chat\Swoole;

use Antonowano\Chat\DataBag;
use OpenSwoole\WebSocket\Frame;

readonly class WsFrame
{
    public function __construct(
        private Frame $swooleFrame,
    ) {
    }

    public function finish(): bool
    {
        return $this->swooleFrame->finish;
    }

    public function data(): DataBag
    {
        return DataBag::fromJson($this->swooleFrame->data);
    }
}

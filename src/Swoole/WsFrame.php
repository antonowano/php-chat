<?php

namespace Antonowano\Chat\Swoole;

readonly class WsFrame
{
    public function __construct(
        private object $swooleFrame,
    ) {
    }

    public function data(): DataBag
    {
        return DataBag::fromJson($this->swooleFrame->data);
    }
}

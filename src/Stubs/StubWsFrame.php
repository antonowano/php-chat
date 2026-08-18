<?php

namespace Antonowano\Chat\Stubs;

use Antonowano\Chat\Stream\WsFrame;

readonly class StubWsFrame implements WsFrame
{
    public function __construct(
        private array $data,
    ) {
    }

    public function data(): string
    {
        return json_encode($this->data);
    }
}

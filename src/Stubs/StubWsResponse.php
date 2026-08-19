<?php

namespace Antonowano\Chat\Stubs;

use Antonowano\Chat\Stream\WsResponse;

class StubWsResponse implements WsResponse
{
    private array $data = [];

    public function push(array $data): void
    {
        $this->data = $data;
    }

    public function data(): array
    {
        return $this->data;
    }
}

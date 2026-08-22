<?php

namespace Antonowano\Chat\Stream;

use Antonowano\Chat\DataBag;

readonly class StreamFrame
{
    private DataBag $data;

    public function __construct(
        WsFrame $frame,
        private string $user = '',
    ) {
        $this->data = DataBag::fromJson($frame->data());
    }

    public function type(): string
    {
        return $this->data->get('type');
    }

    public function data(): DataBag
    {
        return new DataBag($this->data->get('data'));
    }

    public function user(): string
    {
        return $this->user;
    }
}

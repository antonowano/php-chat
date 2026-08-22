<?php

namespace Antonowano\Chat\Stream;

use Antonowano\Chat\DataBag;
use Antonowano\Chat\User;

readonly class StreamFrame
{
    private DataBag $data;

    public function __construct(
        WsFrame $frame,
        private User $user,
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

    public function user(): User
    {
        return $this->user;
    }
}

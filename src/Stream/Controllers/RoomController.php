<?php

namespace Antonowano\Chat\Stream\Controllers;

use Antonowano\Chat\RoomStorage;
use Antonowano\Chat\Stream\StreamFrame;
use Antonowano\Chat\Stream\StreamResponse;

readonly class RoomController
{
    public function __construct(
        private RoomStorage $roomStorage,
    ) {
    }

    public function list(StreamFrame $frame, StreamResponse $response): void
    {
        $data = $frame->data();
        $offset = $data->get('offset', 0);
        $limit = $data->get('limit', 20);
        $rooms = $this->roomStorage->findAllForUser($frame->user(), $offset, $limit);
        $response->sendRoomList($frame->correlationId(), $rooms);
    }
}

<?php

namespace Antonowano\Chat\Swoole;

use Antonowano\Chat\ChatListener;
use Antonowano\Chat\Message;
use Antonowano\Chat\Room;
use Antonowano\Chat\Stream\WsResponse;

readonly class SwooleWsChatListener implements ChatListener
{
    public function __construct(
        private WsResponse $response,
    ) {
    }

    public static function generateId(int $fd): string
    {
        return 'fd' . $fd;
    }

    public function onMessageSent(Message $message): void
    {
        $this->response->push([
            'type' => 'Message',
            'data' => $message->toChatPayload(),
        ]);
    }

    public function onRoomCreated(Room $room): void
    {
        $this->response->push([
            'type' => 'Room',
            'data' => $room->toChatPayload(),
        ]);
    }
}

<?php

namespace Antonowano\Chat\Swoole;

use Antonowano\Chat\ChatListener;
use Antonowano\Chat\Message;
use Antonowano\Chat\Room;
use Antonowano\Chat\Stream\WsResponse;
use Antonowano\Chat\User;

readonly class SwooleWsChatListener implements ChatListener
{
    public function __construct(
        private WsResponse $response,
        private User $user,
    ) {
    }

    public static function generateId(int $fd): string
    {
        return 'fd' . $fd;
    }

    public function onMessageSent(Message $message): void
    {
        if ($message->room()->hasMember($this->user)) {
            $this->response->push([
                'type' => 'Message',
                'data' => $message->toChatPayload(),
            ]);
        }
    }

    public function onRoomCreated(Room $room): void
    {
        if ($room->hasMember($this->user)) {
            $this->response->push([
                'type' => 'Room',
                'data' => $room->toChatPayload(),
            ]);
        }
    }

    public function onUserRemovedFromRoom(User $user, Room $room): void
    {
        if ($room->hasMember($this->user)) {
            $this->response->push([
                'type' => 'RemovedFromRoom',
                'data' => [
                    'userId' => $user->id(),
                    'roomId' => $room->id(),
                ],
            ]);
        }
    }
}

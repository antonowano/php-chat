<?php

namespace Antonowano\Chat\Stream;

use Antonowano\Chat\Message;
use Antonowano\Chat\Room;

readonly class StreamResponse
{
    public function __construct(
        private WsResponse $wsResponse,
    ) {
    }

    /**
     * @param list<Message> $messages
     */
    public function sendMessageList(string $type, array $messages): void
    {
        $this->wsResponse->push([
            'type' => $type,
            'data' => array_map(fn (Message $message) => $message->toChatPayload(), $messages),
        ]);
    }

    /**
     * @param list<Room> $rooms
     */
    public function sendRoomList(array $rooms): void
    {
        $this->wsResponse->push([
            'type' => 'RoomList',
            'data' => array_map(fn (Room $room) => $room->toChatPayload(), $rooms),
        ]);
    }
}

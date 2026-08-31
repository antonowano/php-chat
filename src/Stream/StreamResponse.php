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
    public function sendMessageList(string $correlationId, string $type, array $messages): void
    {
        $this->wsResponse->push([
            'correlationId' => $correlationId,
            'type' => $type,
            'data' => array_map(fn (Message $message) => $message->toChatPayload(), $messages),
        ]);
    }

    /**
     * @param list<Room> $rooms
     */
    public function sendRoomList(string $correlationId, array $rooms): void
    {
        $this->wsResponse->push([
            'correlationId' => $correlationId,
            'type' => 'RoomList',
            'data' => array_map(fn (Room $room) => $room->toChatPayload(), $rooms),
        ]);
    }

    public function sendForbidden(string $correlationId): void
    {
        $this->wsResponse->push([
            'correlationId' => $correlationId,
            'type' => 'Error',
            'data' => 'You dont have permission to access this resource',
        ]);
    }
}

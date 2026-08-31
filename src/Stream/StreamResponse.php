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

    public function sendCreated(string $correlationId): void
    {
        $this->wsResponse->push([
            'correlationId' => $correlationId,
            'status' => 'Success',
        ]);
    }

    /**
     * @param list<Message> $messages
     */
    public function sendMessageList(string $correlationId, array $messages): void
    {
        $this->wsResponse->push([
            'correlationId' => $correlationId,
            'status' => 'Success',
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
            'status' => 'Success',
            'data' => array_map(fn (Room $room) => $room->toChatPayload(), $rooms),
        ]);
    }

    public function sendForbidden(string $correlationId): void
    {
        $this->wsResponse->push([
            'correlationId' => $correlationId,
            'status' => 'Failure',
            'data' => 'You dont have permission to access this resource',
        ]);
    }
}

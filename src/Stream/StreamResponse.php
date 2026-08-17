<?php

namespace Antonowano\Chat\Stream;

use Antonowano\Chat\Message;

readonly class StreamResponse
{
    public function __construct(
        private WsResponse $wsResponse,
    ) {
    }

    public function sendMessageList(array $messages): void
    {
        $this->wsResponse->push([
            'type' => 'LastMessages',
            'data' => array_map(fn (Message $message) => $message->toChatPayload(), $messages),
        ]);
    }
}
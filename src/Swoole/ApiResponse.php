<?php

namespace Antonowano\Chat\Swoole;

use Antonowano\Chat\Message;
use OpenSwoole\Http\Response;

readonly class ApiResponse
{
    public function __construct(
        private Response $swooleResponse,
    ) {
        $this->swooleResponse->header('Content-Type', 'application/json');
    }

    public function messageSent(): void
    {
        $this->swooleResponse->status(200);
        $this->swooleResponse->end(json_encode([
            'status' => 'Success',
        ]));
    }

    /**
     * @param list<Message> $messages
     */
    public function listMessages(array $messages): void
    {
        $this->swooleResponse->status(200);
        $this->swooleResponse->end(json_encode([
            'status' => 'Success',
            'messages' => array_map(fn (Message $message) => $message->toChatPayload(), $messages),
        ]));
    }

    public function routeNotFound(): void
    {
        $this->swooleResponse->status(404);
        $this->swooleResponse->end(json_encode([
            'status' => 'NotFound',
            'error' => 'Route not found',
        ]));
    }
}

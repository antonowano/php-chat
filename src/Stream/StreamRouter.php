<?php

namespace Antonowano\Chat\Stream;

use Antonowano\Chat\Stream\Controllers\MessageController;
use Antonowano\Chat\Stream\Controllers\RoomController;

readonly class StreamRouter
{
    /** @var list<StreamRoute> $routes */
    private array $routes;

    public function __construct(
        MessageController $messageController,
        RoomController $roomController,
    ) {
        $this->routes = [
            new StreamRoute('NewMessage', [$messageController, 'send']),
            new StreamRoute('LastMessages', [$messageController, 'last']),
            new StreamRoute('NextMessages', [$messageController, 'next']),
            new StreamRoute('PreviousMessages', [$messageController, 'previous']),
            new StreamRoute('RoomList', [$roomController, 'list']),
        ];
    }

    public function dispatch(StreamFrame $frame, StreamResponse $response): void
    {
        foreach ($this->routes as $route) {
            if ($frame->type() === $route->type()) {
                $callback = $route->callback();
                $callback($frame, $response);
                return;
            }
        }
    }
}

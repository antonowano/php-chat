<?php

namespace Antonowano\Chat\Stream;

use Antonowano\Chat\Stream\Controllers\MessageController;

readonly class StreamRouter
{
    /** @var list<StreamRoute> $routes */
    private array $routes;

    public function __construct(
        MessageController $controller,
    ) {
        $this->routes = [
            new StreamRoute('NewMessage', [$controller, 'send']),
            new StreamRoute('LastMessages', [$controller, 'last']),
            new StreamRoute('NextMessages', [$controller, 'next']),
            new StreamRoute('PreviousMessages', [$controller, 'previous']),
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

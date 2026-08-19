<?php

namespace Antonowano\Chat\Stream;

readonly class StreamRouter
{
    /** @var list<StreamRoute> $routes */
    private array $routes;

    public function __construct(
        StreamController $controller,
    ) {
        $this->routes = [
            new StreamRoute('NewMessage', [$controller, 'sendMessage']),
            new StreamRoute('LastMessages', [$controller, 'lastMessages']),
            new StreamRoute('NextMessages', [$controller, 'nextMessages']),
            new StreamRoute('PreviousMessages', [$controller, 'previousMessages']),
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

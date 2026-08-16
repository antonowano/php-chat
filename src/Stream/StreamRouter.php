<?php

namespace Antonowano\Chat\Stream;

readonly class StreamRouter
{
    public function __construct(
        /** @var list<StreamRoute> $routes */
        private array $routes = [],
    ) {
    }

    public function dispatch(StreamFrame $frame): void
    {
        foreach ($this->routes as $route) {
            if ($frame->type() === $route->type()) {
                $callback = $route->callback();
                $callback($frame);
                return;
            }
        }
    }
}

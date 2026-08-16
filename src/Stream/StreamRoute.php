<?php

namespace Antonowano\Chat\Stream;

use Closure;

readonly class StreamRoute
{
    private Closure $callback;

    public function __construct(
        private string $type,
        callable $callback,
    ) {
        $this->callback = $callback(...);
    }

    public function type(): string
    {
        return $this->type;
    }

    public function callback(): callable
    {
        return $this->callback;
    }
}

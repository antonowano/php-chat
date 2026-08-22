<?php

namespace Antonowano\Chat;

readonly class User
{
    public function __construct(
        private string $name,
    ) {
    }

    public function equals(User $message): bool
    {
        return $this->name === $message->name;
    }

    public function name(): string
    {
        return $this->name;
    }
}

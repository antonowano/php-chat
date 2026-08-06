<?php

namespace Antonowano\Chat;

use DateTimeInterface;

readonly class Message
{
    public function __construct(
        private int $id,
        private string $text,
        private DateTimeInterface $createdAt,
        private string $author,
    ) {
    }

    public function hasIdGreaterThan(int $id): bool
    {
        return $this->id > $id;
    }

    public function __toString(): string
    {
        return sprintf(
            '[%s] [#%d] %s: %s',
            $this->createdAt->format('H:i:s d.m.Y'),
            $this->id,
            $this->author,
            $this->text
        );
    }
}

<?php

namespace Antonowano\Chat;

use DateTimeInterface;

readonly class Message
{
    public function __construct(
        private string $text,
        private DateTimeInterface $createdAt,
        private string $author,
    ) {
    }

    public function isCreatedAfter(DateTimeInterface $dateTime): bool
    {
        return $this->createdAt > $dateTime;
    }

    public function __toString(): string
    {
        return sprintf(
            '[%s] %s: %s',
            $this->createdAt->format('H:i:s d.m.Y'),
            $this->author,
            $this->text
        );
    }
}

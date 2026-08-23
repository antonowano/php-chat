<?php

namespace Antonowano\Chat;

readonly class NewMessage
{
    public function __construct(
        private int    $roomId,
        private string $text,
        private User   $author,
    ) {
    }

    public function roomId(): int
    {
        return $this->roomId;
    }

    public function text(): string
    {
        return $this->text;
    }

    public function author(): User
    {
        return $this->author;
    }
}

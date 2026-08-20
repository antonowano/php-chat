<?php

namespace Antonowano\Chat;

readonly class NewMessage
{
    public function __construct(
        private int $chatId,
        private string $text,
        private string $author,
    ) {
    }

    public function chatId(): int
    {
        return $this->chatId;
    }

    public function text(): string
    {
        return $this->text;
    }

    public function author(): string
    {
        return $this->author;
    }
}

<?php

namespace Antonowano\Chat;

readonly class NewMessage
{
    public function __construct(
        private string $text,
        private string $author,
    ) {
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

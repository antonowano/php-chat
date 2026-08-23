<?php

namespace Antonowano\Chat;

use DateTimeInterface;

readonly class Message
{
    public function __construct(
        private int               $roomId,
        private int               $id,
        private string            $text,
        private DateTimeInterface $createdAt,
        private User              $author,
    ) {
    }

    public function equals(Message $message): bool
    {
        return $this->id === $message->id
            && $this->text === $message->text
            && $this->createdAt == $message->createdAt
            && $this->author->equals($message->author);
    }

    public function id(): int
    {
        return $this->id;
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

    public function hasIdLessThan(int $id): bool
    {
        return $this->id < $id;
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
            $this->author->name(),
            $this->text
        );
    }

    public function toChatPayload(): array
    {
        return [
            'roomId' => $this->roomId,
            'id' => $this->id,
            'text' => $this->text,
            'author' => $this->author->name(),
            'date' => $this->createdAt->format('d.m.Y'),
            'time' => $this->createdAt->format('H:i'),
        ];
    }
}

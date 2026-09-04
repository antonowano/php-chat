<?php

namespace Antonowano\Chat;

use Antonowano\Chat\Stream\StreamFrame;

readonly class NewMessage
{
    public function __construct(
        private Room $room,
        private string $text,
        private User $author,
    ) {
    }

    public static function createFromStreamFrame(StreamFrame $frame, Room $room): NewMessage
    {
        $data = $frame->data();
        return new NewMessage(
            room: $room,
            text: $data->get('text'),
            author: $frame->user(),
        );
    }

    public function room(): Room
    {
        return $this->room;
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

<?php

namespace Antonowano\Chat\Stream;

use Antonowano\Chat\Chat;
use Antonowano\Chat\NewMessage;

readonly class StreamController
{
    public function __construct(
        private Chat $chat,
    ) {
    }

    public function sendMessage(StreamFrame $frame): void
    {
        $data = $frame->data();
        $this->chat->sendMessage(new NewMessage(
            text: $data->get('text'),
            author: $data->get('author'),
        ));
    }
}

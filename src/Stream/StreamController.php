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

    public function sendMessage(StreamFrame $frame, StreamResponse $response): void
    {
        $data = $frame->data();
        $this->chat->sendMessage(new NewMessage(
            text: $data->get('text'),
            author: $data->get('author'),
        ));
    }

    public function lastMessages(StreamFrame $frame, StreamResponse $response): void
    {
        $messages = $this->chat->getLastMessages(30);
        $response->sendMessageList($messages);
    }
}

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
        $response->sendMessageList('LastMessages', $messages);
    }

    public function nextMessages(StreamFrame $frame, StreamResponse $response): void
    {
        $afterId = $frame->data()->get('id', 0);
        $messages = $this->chat->getMessagesAfterId($afterId, 30);
        $response->sendMessageList('NextMessages', $messages);
    }

    public function previousMessages(StreamFrame $frame, StreamResponse $response): void
    {
        $beforeId = $frame->data()->get('id', 0);
        $messages = $this->chat->getMessagesBeforeId($beforeId, 30);
        $response->sendMessageList('PreviousMessages', $messages);
    }
}

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
            chatId: $data->get('chatId'),
            text: $data->get('text'),
            author: $frame->user(),
        ));
    }

    public function lastMessages(StreamFrame $frame, StreamResponse $response): void
    {
        $chatId = $frame->data()->get('chatId', 0);
        $messages = $this->chat->getLastMessages($chatId, 30);
        $response->sendMessageList('LastMessages', $messages);
    }

    public function nextMessages(StreamFrame $frame, StreamResponse $response): void
    {
        $data = $frame->data();
        $messages = $this->chat->getMessagesAfterId(
            $data->get('chatId', 0),
            $data->get('id', 0),
            30
        );
        $response->sendMessageList('NextMessages', $messages);
    }

    public function previousMessages(StreamFrame $frame, StreamResponse $response): void
    {
        $data = $frame->data();
        $messages = $this->chat->getMessagesBeforeId(
            $data->get('chatId', 0),
            $data->get('id', 0),
            30
        );
        $response->sendMessageList('PreviousMessages', $messages);
    }
}

<?php

namespace Antonowano\Chat;

readonly class ApiController
{
    public function __construct(
        private Chat $chat,
    ) {
    }

    public function sendMessage(ApiRequest $request, ApiResponse $response): void
    {
        $data = $request->json();
        $this->chat->sendMessage(new NewMessage(
            text: $data->get('text'),
            author: $data->get('author'),
        ));
        $response->sendCreated();
    }

    public function lastMessages(ApiRequest $request, ApiResponse $response): void
    {
        $messages = $this->chat->getLastMessages(30);
        $response->sendMessageList($messages);
    }

    public function nextMessages(ApiRequest $request, ApiResponse $response): void
    {
        $afterId = $request->query()->get('id', 0);
        $messages = $this->chat->getMessagesAfterId($afterId, 30);
        $response->sendMessageList($messages);
    }

    public function previousMessages(ApiRequest $request, ApiResponse $response): void
    {
        $beforeId = $request->query()->get('id', 0);
        $messages = $this->chat->getMessagesBeforeId($beforeId, 30);
        $response->sendMessageList($messages);
    }
}

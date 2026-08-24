<?php

namespace Antonowano\Chat\Api\Controllers;

use Antonowano\Chat\Api\ApiRequest;
use Antonowano\Chat\Api\ApiResponse;
use Antonowano\Chat\Events;
use Antonowano\Chat\MessageStorage;
use Antonowano\Chat\NewMessage;

readonly class MessageController
{
    public function __construct(
        private Events $events,
        private MessageStorage $messageStorage,
    ) {
    }

    public function send(ApiRequest $request, ApiResponse $response): void
    {
        $data = $request->json();
        $message = $this->messageStorage->create(new NewMessage(
            roomId: $data->get('roomId'),
            text: $data->get('text'),
            author: $request->user(),
        ));
        $this->events->messageSent($message);
        $response->sendCreated();
    }

    public function last(ApiRequest $request, ApiResponse $response): void
    {
        $roomId = $request->query()->get('roomId', 0);
        $messages = $this->messageStorage->getLastMessages($roomId, 30);
        $response->sendMessageList($messages);
    }

    public function next(ApiRequest $request, ApiResponse $response): void
    {
        $query = $request->query();
        $messages = $this->messageStorage->getMessagesAfterId(
            $query->get('roomId', 0),
            $query->get('id', 0),
            30
        );
        $response->sendMessageList($messages);
    }

    public function previous(ApiRequest $request, ApiResponse $response): void
    {
        $query = $request->query();
        $messages = $this->messageStorage->getMessagesBeforeId(
            $query->get('roomId', 0),
            $query->get('id', 0),
            30
        );
        $response->sendMessageList($messages);
    }
}

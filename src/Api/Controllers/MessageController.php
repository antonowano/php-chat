<?php

namespace Antonowano\Chat\Api\Controllers;

use Antonowano\Chat\AccessControl;
use Antonowano\Chat\Api\ApiRequest;
use Antonowano\Chat\Api\ApiResponse;
use Antonowano\Chat\Events;
use Antonowano\Chat\MessageStorage;
use Antonowano\Chat\NewMessage;
use Antonowano\Chat\RoomStorage;

readonly class MessageController
{
    public function __construct(
        private Events $events,
        private MessageStorage $messageStorage,
        private RoomStorage $roomStorage,
        private AccessControl $accessControl,
    ) {
    }

    public function send(ApiRequest $request, ApiResponse $response): void
    {
        $data = $request->json();
        $roomId = $data->get('roomId');
        $room = $this->roomStorage->findById($roomId);

        if (!$this->accessControl->isGranted($request->user(), 'room.write', $room)) {
            $response->sendForbidden();
            return;
        }

        $message = $this->messageStorage->create(new NewMessage(
            roomId: $roomId,
            text: $data->get('text'),
            author: $request->user(),
        ));
        $this->events->messageSent($message);
        $response->sendCreated();
    }

    public function last(ApiRequest $request, ApiResponse $response): void
    {
        $roomId = $request->query()->get('roomId');
        $room = $this->roomStorage->findById($roomId);

        if (!$this->accessControl->isGranted($request->user(), 'room.read', $room)) {
            $response->sendForbidden();
            return;
        }

        $messages = $this->messageStorage->getLastMessages($roomId, 30);
        $response->sendMessageList($messages);
    }

    public function next(ApiRequest $request, ApiResponse $response): void
    {
        $query = $request->query();
        $roomId = $request->query()->get('roomId');
        $room = $this->roomStorage->findById($roomId);

        if (!$this->accessControl->isGranted($request->user(), 'room.read', $room)) {
            $response->sendForbidden();
            return;
        }

        $messages = $this->messageStorage->getMessagesAfterId(
            $roomId,
            $query->get('id', 0),
            30
        );
        $response->sendMessageList($messages);
    }

    public function previous(ApiRequest $request, ApiResponse $response): void
    {
        $query = $request->query();
        $roomId = $request->query()->get('roomId');
        $room = $this->roomStorage->findById($roomId);

        if (!$this->accessControl->isGranted($request->user(), 'room.read', $room)) {
            $response->sendForbidden();
            return;
        }

        $messages = $this->messageStorage->getMessagesBeforeId(
            $roomId,
            $query->get('id', 0),
            30
        );
        $response->sendMessageList($messages);
    }
}

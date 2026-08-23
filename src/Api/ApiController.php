<?php

namespace Antonowano\Chat\Api;

use Antonowano\Chat\AccessControl;
use Antonowano\Chat\Events;
use Antonowano\Chat\MessageStorage;
use Antonowano\Chat\NewMessage;
use Antonowano\Chat\NewRoom;
use Antonowano\Chat\NewUser;
use Antonowano\Chat\Role;
use Antonowano\Chat\RoomStorage;
use Antonowano\Chat\UserStorage;

readonly class ApiController
{
    public function __construct(
        private Events $events,
        private UserStorage $userStorage,
        private MessageStorage $messageStorage,
        private RoomStorage $roomStorage,
        private AccessControl $accessControl,
    ) {
    }

    public function registerRoom(ApiRequest $request, ApiResponse $response): void
    {
        if (!$this->accessControl->isGranted($request->user(), 'user.register')) {
            $response->sendForbidden();
            return;
        }

        $data = $request->json();
        $room = $this->roomStorage->create(new NewRoom(
            memberIds: $data->get('memberIds'),
        ));
        $this->events->roomCreated($room);
        $response->sendCreated();
    }

    public function registerUser(ApiRequest $request, ApiResponse $response): void
    {
        if (!$this->accessControl->isGranted($request->user(), 'user.register')) {
            $response->sendForbidden();
            return;
        }

        $data = $request->json();
        $accessToken = $this->userStorage->register(new NewUser(
            name: $data->get('name'),
            role: Role::USER,
        ));
        $response->sendAccessToken($accessToken);
    }

    public function sendMessage(ApiRequest $request, ApiResponse $response): void
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

    public function lastMessages(ApiRequest $request, ApiResponse $response): void
    {
        $roomId = $request->query()->get('roomId', 0);
        $messages = $this->messageStorage->getLastMessages($roomId, 30);
        $response->sendMessageList($messages);
    }

    public function nextMessages(ApiRequest $request, ApiResponse $response): void
    {
        $query = $request->query();
        $messages = $this->messageStorage->getMessagesAfterId(
            $query->get('roomId', 0),
            $query->get('id', 0),
            30
        );
        $response->sendMessageList($messages);
    }

    public function previousMessages(ApiRequest $request, ApiResponse $response): void
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

<?php

namespace Antonowano\Chat\Api;

use Antonowano\Chat\Chat;
use Antonowano\Chat\NewMessage;
use Antonowano\Chat\NewUser;
use Antonowano\Chat\Role;
use Antonowano\Chat\UserStorage;

readonly class ApiController
{
    public function __construct(
        private Chat $chat,
        private UserStorage $userStorage,
    ) {
    }

    public function registerUser(ApiRequest $request, ApiResponse $response): void
    {
        $data = $request->json();
        $accessToken = $this->userStorage->register(new NewUser(
            name: $data->get('name'),
            role: Role::USER,
        ));
        $response->sendAccessToken($accessToken);
    }

    public function sendMessage(ApiRequest $request, ApiResponse $response): void
    {
        $accessToken = $request->accessToken();
        $data = $request->json();
        $this->chat->sendMessage(new NewMessage(
            roomId: $data->get('roomId'),
            text: $data->get('text'),
            author: $this->userStorage->findByToken($accessToken),
        ));
        $response->sendCreated();
    }

    public function lastMessages(ApiRequest $request, ApiResponse $response): void
    {
        $roomId = $request->query()->get('roomId', 0);
        $messages = $this->chat->getLastMessages($roomId, 30);
        $response->sendMessageList($messages);
    }

    public function nextMessages(ApiRequest $request, ApiResponse $response): void
    {
        $query = $request->query();
        $messages = $this->chat->getMessagesAfterId(
            $query->get('roomId', 0),
            $query->get('id', 0),
            30
        );
        $response->sendMessageList($messages);
    }

    public function previousMessages(ApiRequest $request, ApiResponse $response): void
    {
        $query = $request->query();
        $messages = $this->chat->getMessagesBeforeId(
            $query->get('roomId', 0),
            $query->get('id', 0),
            30
        );
        $response->sendMessageList($messages);
    }
}

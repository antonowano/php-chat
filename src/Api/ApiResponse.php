<?php

namespace Antonowano\Chat\Api;

use Antonowano\Chat\Enums\HttpStatusCode;
use Antonowano\Chat\Message;
use Antonowano\Chat\Room;

readonly class ApiResponse
{
    public function __construct(
        private HttpResponse $httpResponse,
    ) {
    }

    public function sendCreated(): void
    {
        $this->httpResponse->json([], HttpStatusCode::CREATED);
    }

    /**
     * @param list<Message> $messages
     */
    public function sendMessageList(array $messages): void
    {
        $this->httpResponse->json([
            'messages' => array_map(fn (Message $message) => $message->toChatPayload(), $messages),
        ], HttpStatusCode::OK);
    }

    public function sendRouteNotFound(): void
    {
        $this->httpResponse->json([
            'error' => 'Route not found',
        ], HttpStatusCode::NOT_FOUND);
    }

    public function sendAccessToken(string $accessToken): void
    {
        $this->httpResponse->json([
            'accessToken' => $accessToken,
        ], HttpStatusCode::CREATED);
    }

    public function sendForbidden(): void
    {
        $this->httpResponse->json([
            'error' => 'You dont have permission to access this resource',
        ], HttpStatusCode::FORBIDDEN);
    }

    /**
     * @param array $rooms
     * @return void
     */
    public function sendRoomList(array $rooms): void
    {
        $this->httpResponse->json([
            'rooms' => array_map(fn (Room $r) => $r->toChatPayload(), $rooms),
        ], HttpStatusCode::OK);
    }
}

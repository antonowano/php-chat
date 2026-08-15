<?php

namespace Antonowano\Chat;

readonly class Responses
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
}

<?php

namespace Antonowano\Chat\Api;

use Antonowano\Chat\Enums\HttpStatusCode;
use Antonowano\Chat\User;

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

    public function sendExecuted(): void
    {
        $this->httpResponse->json([], HttpStatusCode::OK);
    }

    public function sendRouteNotFound(): void
    {
        $this->httpResponse->json([
            'error' => 'Route not found',
        ], HttpStatusCode::NOT_FOUND);
    }

    public function sendRegisteredUser(User $user): void
    {
        $this->httpResponse->json([
            'user' => $user->toChatPayload(),
            'accessToken' => $user->accessToken(),
        ], HttpStatusCode::CREATED);
    }

    public function sendForbidden(): void
    {
        $this->httpResponse->json([
            'error' => 'You dont have permission to access this resource',
        ], HttpStatusCode::FORBIDDEN);
    }
}

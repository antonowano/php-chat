<?php

namespace Antonowano\Chat\Api\Controllers;

use Antonowano\Chat\AccessControl;
use Antonowano\Chat\Api\ApiRequest;
use Antonowano\Chat\Api\ApiResponse;
use Antonowano\Chat\NewUser;
use Antonowano\Chat\Role;
use Antonowano\Chat\UserStorage;

readonly class UserController
{
    public function __construct(
        private UserStorage $userStorage,
        private AccessControl $accessControl,
    ) {
    }

    public function register(ApiRequest $request, ApiResponse $response): void
    {
        if (!$this->accessControl->isGranted($request->user(), 'user.register')) {
            $response->sendForbidden();
            return;
        }

        $data = $request->json();
        $accessToken = $this->userStorage->create(new NewUser(
            name: $data->get('name'),
            role: Role::USER,
        ))->accessToken();
        $response->sendAccessToken($accessToken);
    }
}

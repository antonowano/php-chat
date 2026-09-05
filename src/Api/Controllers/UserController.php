<?php

namespace Antonowano\Chat\Api\Controllers;

use Antonowano\Chat\AccessControl;
use Antonowano\Chat\Api\ApiRequest;
use Antonowano\Chat\Api\ApiResponse;
use Antonowano\Chat\NewUser;
use Antonowano\Chat\RoomStorage;
use Antonowano\Chat\UserStorage;

readonly class UserController
{
    public function __construct(
        private UserStorage $userStorage,
        private RoomStorage $roomStorage,
        private AccessControl $accessControl,
    ) {
    }

    public function register(ApiRequest $request, ApiResponse $response): void
    {
        if (!$this->accessControl->isGranted($request->user(), 'user.register')) {
            $response->sendForbidden();
            return;
        }

        $user = $this->userStorage->create(NewUser::createFromApiRequest($request));
        $response->sendRegisteredUser($user);
    }

    public function remove(ApiRequest $request, ApiResponse $response): void
    {
        $data = $request->json();
        $userId = $data->get('userId');
        $user = $this->userStorage->findById($userId);

        if (!$this->accessControl->isGranted($request->user(), 'user.remove', $user)) {
            $response->sendForbidden();
            return;
        }

        $this->roomStorage->removeMemberFromAllRooms($user);
        $this->userStorage->remove($user);
        $response->sendExecuted();
    }
}

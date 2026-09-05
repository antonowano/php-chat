<?php

namespace Antonowano\Chat\Api\Controllers;

use Antonowano\Chat\AccessControl;
use Antonowano\Chat\Api\ApiRequest;
use Antonowano\Chat\Api\ApiResponse;
use Antonowano\Chat\Events;
use Antonowano\Chat\NewRoom;
use Antonowano\Chat\RoomStorage;
use Antonowano\Chat\UserStorage;

readonly class RoomController
{
    public function __construct(
        private Events $events,
        private RoomStorage $roomStorage,
        private UserStorage $userStorage,
        private AccessControl $accessControl,
    ) {
    }

    public function register(ApiRequest $request, ApiResponse $response): void
    {
        if (!$this->accessControl->isGranted($request->user(), 'room.register')) {
            $response->sendForbidden();
            return;
        }

        $room = $this->roomStorage->create(NewRoom::createFromApiRequest($request));
        $this->events->roomCreated($room);
        $response->sendCreated();
    }

    public function remove(ApiRequest $request, ApiResponse $response): void
    {
        $data = $request->json();
        $roomId = $data->get('roomId');
        $room = $this->roomStorage->findById($roomId);

        if (!$this->accessControl->isGranted($request->user(), 'room.remove', $room)) {
            $response->sendForbidden();
            return;
        }

        $this->roomStorage->remove($room);
        $this->events->removedRoom($room);
        $response->sendExecuted();
    }

    public function removeUser(ApiRequest $request, ApiResponse $response): void
    {
        $data = $request->json();
        $roomId = $data->get('roomId');
        $room = $this->roomStorage->findById($roomId);

        if (!$this->accessControl->isGranted($request->user(), 'room.remove-user', $room)) {
            $response->sendForbidden();
            return;
        }

        $userId = $data->get('userId');
        $user = $this->userStorage->findById($userId);
        $updatedRoom = $room->removeMember($user);
        $this->roomStorage->save($updatedRoom);
        $this->events->userRemovedFromRoom($user, $room);
        $response->sendExecuted();
    }
}

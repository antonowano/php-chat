<?php

namespace Antonowano\Chat\Api\Controllers;

use Antonowano\Chat\AccessControl;
use Antonowano\Chat\Api\ApiRequest;
use Antonowano\Chat\Api\ApiResponse;
use Antonowano\Chat\Events;
use Antonowano\Chat\NewRoom;
use Antonowano\Chat\RoomStorage;

readonly class RoomController
{
    public function __construct(
        private Events $events,
        private RoomStorage $roomStorage,
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
}

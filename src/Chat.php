<?php

namespace Antonowano\Chat;

use Antonowano\Chat\Api\ApiRouter;
use Antonowano\Chat\Api\Controllers\MessageController as ApiMessageController;
use Antonowano\Chat\Api\Controllers\RoomController as ApiRoomController;
use Antonowano\Chat\Api\Controllers\UserController as ApiUserController;
use Antonowano\Chat\Stream\Controllers\MessageController as StreamMessageController;
use Antonowano\Chat\Stream\Controllers\RoomController as StreamRoomController;
use Antonowano\Chat\Stream\StreamRouter;
use Psr\Clock\ClockInterface;

readonly class Chat
{
    private MessageStorage $messageStorage;
    private Events $events;
    private UserStorage $userStorage;
    private SessionStorage $sessionStorage;
    private RoomStorage $roomStorage;
    private ApiRouter $apiRouter;
    private StreamRouter $streamRouter;

    public function __construct(
        ClockInterface $clock,
    ) {
        $this->events = new Events();
        $this->userStorage = new UserStorage();
        $this->sessionStorage = new SessionStorage();
        $this->roomStorage = new RoomStorage($this->userStorage);
        $this->messageStorage = new MessageStorage($clock, $this->roomStorage);
        $accessControl = new AccessControl();
        $this->apiRouter = new ApiRouter(
            new ApiMessageController(
                $this->events,
                $this->messageStorage,
            ),
            new ApiUserController(
                $this->userStorage,
                $accessControl,
            ),
            new ApiRoomController(
                $this->events,
                $this->roomStorage,
                $accessControl,
            )
        );
        $this->streamRouter = new StreamRouter(
            new StreamMessageController(
                $this->events,
                $this->messageStorage,
            ),
            new StreamRoomController(
                $this->roomStorage,
            ),
        );
    }

    public function messageStorage(): MessageStorage
    {
        return $this->messageStorage;
    }

    public function events(): Events
    {
        return $this->events;
    }

    public function userStorage(): UserStorage
    {
        return $this->userStorage;
    }

    public function sessionStorage(): SessionStorage
    {
        return $this->sessionStorage;
    }

    public function roomStorage(): RoomStorage
    {
        return $this->roomStorage;
    }

    public function apiRouter(): ApiRouter
    {
        return $this->apiRouter;
    }

    public function streamRouter(): StreamRouter
    {
        return $this->streamRouter;
    }
}

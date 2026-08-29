<?php

namespace Antonowano\Chat\Api;

use Antonowano\Chat\Api\Controllers\MessageController;
use Antonowano\Chat\Api\Controllers\RoomController;
use Antonowano\Chat\Api\Controllers\UserController;

readonly class ApiRouter
{
    /** @var list<ApiRoute> */
    private array $routes;

    public function __construct(
        MessageController $messageController,
        UserController $userController,
        RoomController $roomController,
    ) {
        $this->routes = [
            new ApiRoute('POST', '/api/room/register', [$roomController, 'register']),
            new ApiRoute('GET', '/api/rooms', [$roomController, 'list']),
            new ApiRoute('POST', '/api/user/register', [$userController, 'register']),
            new ApiRoute('POST', '/api/message/send', [$messageController, 'send']),
            new ApiRoute('GET', '/api/messages/last', [$messageController, 'last']),
            new ApiRoute('GET', '/api/messages/next', [$messageController, 'next']),
            new ApiRoute('GET', '/api/messages/previous', [$messageController, 'previous']),
        ];
    }

    public function dispatch(ApiRequest $request, ApiResponse $response): void
    {
        foreach ($this->routes as $route) {
            if ($request->routeMatches($route)) {
                $callback = $route->callback();
                $callback($request, $response);
                return;
            }
        }

        $response->sendRouteNotFound();
    }
}

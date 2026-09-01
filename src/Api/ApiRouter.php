<?php

namespace Antonowano\Chat\Api;

use Antonowano\Chat\Api\Controllers\RoomController;
use Antonowano\Chat\Api\Controllers\UserController;

readonly class ApiRouter
{
    /** @var list<ApiRoute> */
    private array $routes;

    public function __construct(
        UserController $userController,
        RoomController $roomController,
    ) {
        $this->routes = [
            new ApiRoute('POST', '/api/room/register', [$roomController, 'register']),
            new ApiRoute('POST', '/api/user/register', [$userController, 'register']),
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

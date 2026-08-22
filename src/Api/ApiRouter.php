<?php

namespace Antonowano\Chat\Api;

readonly class ApiRouter
{
    /** @var list<ApiRoute> */
    private array $routes;

    public function __construct(
        ApiController $apiController,
    ) {
        $this->routes = [
            new ApiRoute('POST', '/api/user/register', [$apiController, 'registerUser']),
            new ApiRoute('POST', '/api/message/send', [$apiController, 'sendMessage']),
            new ApiRoute('GET', '/api/messages/last', [$apiController, 'lastMessages']),
            new ApiRoute('GET', '/api/messages/next', [$apiController, 'nextMessages']),
            new ApiRoute('GET', '/api/messages/previous', [$apiController, 'previousMessages']),
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

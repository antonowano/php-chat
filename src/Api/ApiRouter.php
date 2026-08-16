<?php

namespace Antonowano\Chat\Api;

readonly class ApiRouter
{
    public function __construct(
        /** @var list<ApiRoute> */
        private array $routes,
    ) {
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

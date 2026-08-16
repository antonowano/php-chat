<?php

namespace Tests\Antonowano\Chat\Unit\Api;

use Antonowano\Chat\Api\ApiController;
use Antonowano\Chat\Api\ApiRequest;
use Antonowano\Chat\Api\ApiResponse;
use Antonowano\Chat\Api\ApiRoute;
use Antonowano\Chat\Api\ApiRouter;
use Tests\Antonowano\Chat\Unit\TestCase;

class ApiRouterTest extends TestCase
{
    public function testAddAndDispatch(): void
    {
        $request = $this->createMock(ApiRequest::class);
        $request->method('routeMatches')->willReturn(true);
        $response = $this->createStub(ApiResponse::class);
        $controller = $this->createMock(ApiController::class);
        $controller->expects($this->once())->method('lastMessages');
        $router = new ApiRouter([
            new ApiRoute('GET', '/api/profile', [$controller, 'lastMessages']),
        ]);
        $router->dispatch($request, $response);
    }
}

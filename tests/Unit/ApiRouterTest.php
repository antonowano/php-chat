<?php

namespace Tests\Antonowano\Chat\Unit;

use Antonowano\Chat\ApiController;
use Antonowano\Chat\ApiRequest;
use Antonowano\Chat\ApiResponse;
use Antonowano\Chat\ApiRouter;
use Antonowano\Chat\ApiRoute;

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

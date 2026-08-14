<?php

namespace Tests\Antonowano\Chat\Unit\Swoole;

use Antonowano\Chat\Swoole\ApiResponse;
use OpenSwoole\Http\Response;
use Tests\Antonowano\Chat\Unit\TestCase;

class ApiResponseTest extends TestCase
{
    private Response $swooleResponse;
    private ApiResponse $apiResponse;

    protected function setUp(): void
    {
        parent::setUp();
        $this->swooleResponse = $this->createMock(Response::class);
        $this->swooleResponse->expects($this->once())->method('header')->with(
            $this->equalTo('Content-Type'),
            $this->equalTo('application/json'),
        );
        $this->apiResponse = new ApiResponse($this->swooleResponse);
    }

    public function testMessageSent(): void
    {
        $this->swooleResponse->expects($this->once())->method('end')->with(
            $this->equalTo(json_encode([
                'status' => 'Success',
            ])),
        );
        $this->swooleResponse->expects($this->once())->method('status')->with(
            $this->equalTo(200),
        );
        $this->apiResponse->messageSent();
    }

    public function testListMessages(): void
    {
        $messages = $this->createMessages([1, 2, 3]);
        $this->swooleResponse->expects($this->once())->method('end')->with(
            $this->callback(function (string $json) {
                $data = json_decode($json, true);
                return $data['status'] === 'Success'
                    && count($data['messages']) === 3;
            }),
        );
        $this->swooleResponse->expects($this->once())->method('status')->with(
            $this->equalTo(200),
        );
        $this->apiResponse->listMessages($messages);
    }

    public function testRouteNotFound(): void
    {
        $this->swooleResponse->expects($this->once())->method('end')->with(
            $this->callback(function (string $json) {
                $data = json_decode($json, true);
                return $data['status'] === 'NotFound'
                    && is_string($data['error'])
                    && !empty($data['error']);
            }),
        );
        $this->swooleResponse->expects($this->once())->method('status')->with(
            $this->equalTo(404),
        );
        $this->apiResponse->routeNotFound();
    }
}

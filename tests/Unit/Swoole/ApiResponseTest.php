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
        $this->apiResponse = new ApiResponse($this->swooleResponse);
    }

    public function testJsonResponse(): void
    {
        $data = [
            'status' => 'Success',
            'data' => [
                'id' => 1,
            ],
        ];

        $this->swooleResponse->expects($this->once())->method('header')->with(
            $this->equalTo('Content-Type'),
            $this->equalTo('application/json'),
        );
        $this->swooleResponse->expects($this->once())->method('status')->with(
            $this->equalTo(201),
        );
        $this->swooleResponse->expects($this->once())->method('end')->with(
            $this->equalTo(json_encode($data)),
        );

        $this->apiResponse->json($data, 201);
    }
}

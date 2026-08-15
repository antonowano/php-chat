<?php

namespace Tests\Antonowano\Chat\Unit\Swoole;

use Antonowano\Chat\HttpContentType;
use Antonowano\Chat\HttpHeader;
use Antonowano\Chat\HttpStatusCode;
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

    public function testJson(): void
    {
        $status = HttpStatusCode::CREATED;
        $data = [
            'id' => 1,
        ];

        $this->swooleResponse->expects($this->once())->method('header')->with(
            $this->equalTo(HttpHeader::CONTENT_TYPE->value),
            $this->equalTo(HttpContentType::JSON->value),
        );
        $this->swooleResponse->expects($this->once())->method('status')->with(
            $this->equalTo($status->value),
        );
        $this->swooleResponse->expects($this->once())->method('end')->with(
            $this->equalTo(json_encode($data)),
        );

        $this->apiResponse->json($data, $status);
    }
}

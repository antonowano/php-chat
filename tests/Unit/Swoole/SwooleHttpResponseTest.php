<?php

namespace Tests\Antonowano\Chat\Unit\Swoole;

use Antonowano\Chat\Api\HttpContentType;
use Antonowano\Chat\Api\HttpHeader;
use Antonowano\Chat\Api\HttpStatusCode;
use Antonowano\Chat\Swoole\SwooleHttpResponse;
use OpenSwoole\Http\Response;
use Tests\Antonowano\Chat\Unit\TestCase;

class SwooleHttpResponseTest extends TestCase
{
    private Response $swooleResponse;
    private SwooleHttpResponse $response;

    protected function setUp(): void
    {
        parent::setUp();
        $this->swooleResponse = $this->createMock(Response::class);
        $this->response = new SwooleHttpResponse($this->swooleResponse);
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

        $this->response->json($data, $status);
    }
}

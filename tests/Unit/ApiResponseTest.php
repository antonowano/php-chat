<?php

namespace Tests\Antonowano\Chat\Unit;

use Antonowano\Chat\HttpResponse;
use Antonowano\Chat\HttpStatusCode;
use Antonowano\Chat\ApiResponse;

class ApiResponseTest extends TestCase
{
    private HttpResponse $httpResponse;
    private ApiResponse $response;

    protected function setUp(): void
    {
        parent::setUp();
        $this->httpResponse = $this->createMock(HttpResponse::class);
        $this->response = new ApiResponse($this->httpResponse);
    }

    public function testSendCreated(): void
    {
        $this->httpResponse->expects($this->once())->method('json')->with(
            $this->anything(),
            $this->equalTo(HTTPStatusCode::CREATED),
        );
        $this->response->sendCreated();
    }

    public function testSendMessageList(): void
    {
        $messages = $this->createMessages([1, 2]);
        $this->httpResponse->expects($this->once())->method('json')->with(
            $this->callback(function (array $data) use (&$messages) {
                return count($data['messages']) === 2
                    && $data['messages'][0] === $messages[0]->toChatPayload()
                    && $data['messages'][1] === $messages[1]->toChatPayload();
            }),
            $this->equalTo(HTTPStatusCode::OK),
        );
        $this->response->sendMessageList($messages);
    }

    public function testSendRouteNotFound(): void
    {
        $this->httpResponse->expects($this->once())->method('json')->with(
            $this->callback(function (array $data) {
                return str_contains(strtolower($data['error']), 'route');
            }),
            $this->equalTo(HttpStatusCode::NOT_FOUND),
        );
        $this->response->sendRouteNotFound();
    }
}

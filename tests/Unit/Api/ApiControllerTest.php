<?php

namespace Tests\Antonowano\Chat\Unit\Api;

use Antonowano\Chat\Api\ApiController;
use Antonowano\Chat\Api\ApiRequest;
use Antonowano\Chat\Api\ApiResponse;
use Antonowano\Chat\Chat;
use Antonowano\Chat\DataBag;
use Tests\Antonowano\Chat\Unit\TestCase;

class ApiControllerTest extends TestCase
{
    private Chat $chat;
    private ApiController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->chat = $this->createMock(Chat::class);
        $this->controller = new ApiController($this->chat);
    }

    public function testSendMessage(): void
    {
        $data = [
            'text' => 'Hello world',
            'author' => 'Ivan',
        ];

        $request = $this->createMock(ApiRequest::class);
        $request->method('json')->willReturn(new DataBag($data));

        $response = $this->createMock(ApiResponse::class);
        $response->expects($this->once())->method('sendCreated');

        $this->chat->expects($this->once())->method('sendMessage')
            ->with(
                $this->createNewMessage($data['text'], $data['author']),
            );

        $this->controller->sendMessage($request, $response);
    }

    public function testLastMessages(): void
    {
        $messages = $this->createMessages([1, 2, 3]);

        $request = $this->createStub(ApiRequest::class);
        $response = $this->createMock(ApiResponse::class);
        $response->expects($this->once())->method('sendMessageList')
            ->with($messages);
        $this->chat->expects($this->once())->method('getLastMessages')
            ->willReturn($messages);

        $this->controller->lastMessages($request, $response);
    }

    public function testNextMessages(): void
    {
        $afterId = 123;
        $messages = $this->createMessages([1, 2, 3]);

        $request = $this->createMock(ApiRequest::class);
        $request->method('query')->willReturn(new DataBag([ 'id' => $afterId ]));

        $response = $this->createMock(ApiResponse::class);
        $response->expects($this->once())->method('sendMessageList')
            ->with($messages);

        $this->chat->expects($this->once())->method('getMessagesAfterId')
            ->with($afterId)
            ->willReturn($messages);

        $this->controller->nextMessages($request, $response);
    }

    public function testPreviousMessages(): void
    {
        $beforeId = 123;
        $messages = $this->createMessages([1, 2, 3]);

        $request = $this->createMock(ApiRequest::class);
        $request->method('query')->willReturn(new DataBag([ 'id' => $beforeId ]));

        $response = $this->createMock(ApiResponse::class);
        $response->expects($this->once())->method('sendMessageList')
            ->with($messages);

        $this->chat->expects($this->once())->method('getMessagesBeforeId')
            ->with($beforeId)
            ->willReturn($messages);

        $this->controller->previousMessages($request, $response);
    }
}

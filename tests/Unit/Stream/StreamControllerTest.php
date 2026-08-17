<?php

namespace Tests\Antonowano\Chat\Unit\Stream;

use Antonowano\Chat\Chat;
use Antonowano\Chat\DataBag;
use Antonowano\Chat\Stream\StreamController;
use Antonowano\Chat\Stream\StreamFrame;
use Antonowano\Chat\Stream\StreamResponse;
use Tests\Antonowano\Chat\Unit\TestCase;

class StreamControllerTest extends TestCase
{
    private Chat $chat;
    private StreamController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->chat = $this->createMock(Chat::class);
        $this->controller = new StreamController($this->chat);
    }

    public function testSendMessage(): void
    {
        $data = [
            'text' => 'message',
            'author' => 'User',
        ];
        $this->chat->expects($this->once())->method('sendMessage')
            ->with($this->createNewMessage(...$data));
        $response = $this->createStub(StreamResponse::class);
        $frame = $this->createMock(StreamFrame::class);
        $frame->expects($this->once())->method('data')
            ->willReturn(new DataBag($data));
        $this->controller->sendMessage($frame, $response);
    }
}

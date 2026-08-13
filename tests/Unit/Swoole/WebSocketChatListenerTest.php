<?php

namespace Tests\Antonowano\Chat\Unit\Swoole;

use Antonowano\Chat\Swoole\WebSocketChatListener;
use Tests\Antonowano\Chat\Unit\TestCase;

class WebSocketChatListenerTest extends TestCase
{
    private object $server;
    private WebSocketChatListener $listener;

    protected function setUp(): void
    {
        parent::setUp();
        $this->server = $this->createMock('Swoole\WebSocket\Server');
        $this->listener = new WebSocketChatListener(
            server: $this->server,
            fd: 23,
        );
    }

    public function testId()
    {
        $this->assertSame('fd23', $this->listener->id());
    }

    public function testGenerateId()
    {
        $this->assertSame('fd4893', WebSocketChatListener::generateId(4893));
        $this->assertSame('fd132', WebSocketChatListener::generateId(132));
    }

    public function testOnMessageSent()
    {
        $message = $this->createMessage(id: 100);
        $this->server->expects($this->once())->method('push')->with(
            $this->equalTo(23),
            $this->equalTo(json_encode([
                'type' => 'Message',
                'message' => $message->toChatPayload(),
            ])),
        );
        $this->listener->onMessageSent($message);
    }
}

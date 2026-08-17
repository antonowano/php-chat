<?php

namespace Tests\Antonowano\Chat\Unit\Swoole;

use Antonowano\Chat\Swoole\SwooleWsChatListener;
use OpenSwoole\WebSocket\Server;
use Tests\Antonowano\Chat\Unit\TestCase;

class SwooleWsChatListenerTest extends TestCase
{
    private Server $server;
    private SwooleWsChatListener $listener;

    protected function setUp(): void
    {
        parent::setUp();
        $this->server = $this->createMock(Server::class);
        $this->listener = new SwooleWsChatListener(
            server: $this->server,
            fd: 23,
        );
    }

    public function testGenerateId()
    {
        $this->assertSame('fd4893', SwooleWsChatListener::generateId(4893));
        $this->assertSame('fd132', SwooleWsChatListener::generateId(132));
    }

    public function testOnMessageSent()
    {
        $message = $this->createMessage(id: 100);
        $this->server->expects($this->once())->method('push')->with(
            $this->equalTo(23),
            $this->equalTo(json_encode([
                'type' => 'Message',
                'data' => $message->toChatPayload(),
            ])),
        );
        $this->listener->onMessageSent($message);
    }
}

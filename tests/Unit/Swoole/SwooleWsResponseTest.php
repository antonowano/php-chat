<?php

namespace Tests\Antonowano\Chat\Unit\Swoole;

use Antonowano\Chat\Swoole\SwooleWsResponse;
use OpenSwoole\WebSocket\Server;
use Tests\Antonowano\Chat\Unit\TestCase;

class SwooleWsResponseTest extends TestCase
{
    public function testPush(): void
    {
        $fd = 123;
        $data = [
            'id' => 123,
            'text' => 'test message',
        ];
        $swooleServer = $this->createMock(Server::class);
        $swooleServer->expects($this->once())->method('push')->with($fd, json_encode($data));

        $response = new SwooleWsResponse($swooleServer, $fd);
        $response->push($data);
    }
}

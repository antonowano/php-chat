<?php

namespace Tests\Antonowano\Chat\Unit\Swoole;

use Antonowano\Chat\Swoole\DataBag;
use Antonowano\Chat\Swoole\WsFrame;
use Tests\Antonowano\Chat\Unit\TestCase;

class WsFrameTest extends TestCase
{
    /** @var \OpenSwoole\WebSocket\Frame */
    private object $swooleFrame;
    private WsFrame $wsFrame;

    protected function setUp(): void
    {
        parent::setUp();
        $this->swooleFrame = $this->createMock('OpenSwoole\WebSocket\Frame');
        $this->wsFrame = new WsFrame($this->swooleFrame);
    }

    public function testDataReturnDataBag()
    {
        $data = [
            'type' => 'newMessage',
            'newMessage' => [
                'text' => 'Hello World!',
                'author' => 'Ivan',
            ],
        ];
        $this->swooleFrame->data = json_encode($data);

        $this->assertObjectEquals(new DataBag($data), $this->wsFrame->data());
    }
}

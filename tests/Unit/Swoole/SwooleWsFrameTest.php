<?php

namespace Tests\Antonowano\Chat\Unit\Swoole;

use Antonowano\Chat\DataBag;
use Antonowano\Chat\Swoole\SwooleWsFrame;
use OpenSwoole\WebSocket\Frame;
use Tests\Antonowano\Chat\Unit\TestCase;

class SwooleWsFrameTest extends TestCase
{
    private Frame $swooleFrame;
    private SwooleWsFrame $wsFrame;

    protected function setUp(): void
    {
        parent::setUp();
        $this->swooleFrame = $this->createMock(Frame::class);
        $this->wsFrame = new SwooleWsFrame($this->swooleFrame);
    }

    public function testFinished()
    {
        $this->swooleFrame->finish = true;
        $this->assertTrue($this->wsFrame->finish());
    }

    public function testNotFinished()
    {
        $this->swooleFrame->finish = false;
        $this->assertFalse($this->wsFrame->finish());
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

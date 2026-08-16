<?php

namespace Tests\Antonowano\Chat\Unit\Swoole;

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

    public function testDataReturnDataBag()
    {
        $data = 'Any data';
        $this->swooleFrame->data = $data;
        $this->assertSame($data, $this->wsFrame->data());
    }
}

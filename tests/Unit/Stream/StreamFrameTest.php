<?php

namespace Tests\Antonowano\Chat\Unit\Stream;

use Antonowano\Chat\DataBag;
use Antonowano\Chat\Stream\WsFrame;
use Antonowano\Chat\Stream\StreamFrame;
use Tests\Antonowano\Chat\Unit\TestCase;

class StreamFrameTest extends TestCase
{
    public function testTypeReturnsTypeString(): void
    {
        $data = [
            'type' => 'NewMessage',
            'data' => [
                'id' => 123,
                'name' => 'User',
                'read' => true,
            ],
        ];
        $swooleFrame = $this->createMock(WsFrame::class);
        $swooleFrame->expects($this->once())->method('data')
            ->willReturn(json_encode($data));
        $frame = new StreamFrame($swooleFrame);
        $this->assertEquals($data['type'], $frame->type());
    }

    public function testDataReturnsDataBag(): void
    {
        $data = [
            'type' => 'NewMessage',
            'data' => [
                'id' => 123,
                'name' => 'User',
                'read' => true,
            ],
        ];
        $swooleFrame = $this->createMock(WsFrame::class);
        $swooleFrame->expects($this->once())->method('data')
            ->willReturn(json_encode($data));
        $frame = new StreamFrame($swooleFrame);
        $this->assertObjectEquals(new DataBag($data['data']), $frame->data());
    }
}

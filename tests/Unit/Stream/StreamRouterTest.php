<?php

namespace Tests\Antonowano\Chat\Unit\Stream;

use Antonowano\Chat\Stream\StreamFrame;
use Antonowano\Chat\Stream\StreamRoute;
use Antonowano\Chat\Stream\StreamRouter;
use Tests\Antonowano\Chat\Unit\TestCase;

class StreamRouterTest extends TestCase
{
    public function testDispatchWillExecuteCallback(): void
    {
        $type = 'isBurger';
        $executed1 = false;
        $executed2 = false;
        $frame = $this->createMock(StreamFrame::class);
        $frame->expects($this->once())->method('type')->willReturn($type);
        $router = new StreamRouter([
            new StreamRoute($type, function () use (&$executed1) {
                $executed1 = true;
            }),
            new StreamRoute('otherType', function () use (&$executed2) {
                $executed2 = true;
            }),
        ]);
        $router->dispatch($frame);
        $this->assertTrue($executed1);
        $this->assertFalse($executed2);
    }
}

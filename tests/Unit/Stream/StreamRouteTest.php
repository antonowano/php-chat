<?php

namespace Tests\Antonowano\Chat\Unit\Stream;

use Antonowano\Chat\Stream\StreamRoute;
use Tests\Antonowano\Chat\Unit\TestCase;

class StreamRouteTest extends TestCase
{
    public function testStreamRoute(): void
    {
        $type = 'newMessage';
        $message = 'test message';
        $route = new StreamRoute('newMessage', fn () => $message);
        $this->assertSame($type, $route->type());
        $this->assertIsCallable($route->callback());
        $this->assertSame($message, $route->callback()($message));
    }
}

<?php

namespace Tests\Antonowano\Chat\Unit;

class MessageTest extends TestCase
{
    public function testToString()
    {
        $message = $this->createMessage(id: 15432, datetime: '2026-08-05 21:22:13');

        $this->assertSame('[21:22:13 05.08.2026] [#15432] User: Text message', (string) $message);
    }

    public function testHasIdGreaterThanReturnsTrueWhenIdIsGreater(): void
    {
        $message = $this->createMessage(id: 10);

        $this->assertTrue($message->hasIdGreaterThan(5));
        $this->assertTrue($message->hasIdGreaterThan(9));
        $this->assertTrue($message->hasIdGreaterThan(-100));
    }

    public function testHasIdGreaterThanReturnsFalseWhenIdIsEqual(): void
    {
        $message = $this->createMessage(id: 10);

        $this->assertFalse($message->hasIdGreaterThan(10));
    }

    public function testHasIdGreaterThanReturnsFalseWhenIdIsLess(): void
    {
        $message = $this->createMessage(id: 10);

        $this->assertFalse($message->hasIdGreaterThan(15));
        $this->assertFalse($message->hasIdGreaterThan(100));
    }
}

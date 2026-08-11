<?php

namespace Tests\Antonowano\Chat\Unit;

class MessageTest extends TestCase
{
    public function testToString()
    {
        $message = $this->createMessage(id: 15432, datetime: '2026-08-05 21:22:13');

        $this->assertSame('[21:22:13 05.08.2026] [#15432] User: Text message', (string) $message);
    }

    public function testObjectEquals(): void
    {
        $message1 = $this->createMessage(id: 10, datetime: '2026-08-07 21:00:00');
        $message2 = $this->createMessage(id: 10, datetime: '2026-08-07 21:00:00');

        $this->assertObjectEquals($message1, $message2);
    }

    public function testObjectNotEquals(): void
    {
        $message1 = $this->createMessage(id: 10);
        $message2 = $this->createMessage(id: 11);

        $this->assertObjectNotEquals($message1, $message2);
    }

    public function testHasIdLessThanReturnsTrueWhenMessageIdIsLess(): void
    {
        $message = $this->createMessage(id: 10);

        $this->assertTrue($message->hasIdLessThan(11));
        $this->assertTrue($message->hasIdLessThan(100));
    }

    public function testHasIdLessThanReturnsFalseWhenMessageIdIsEqual(): void
    {
        $message = $this->createMessage(id: 10);

        $this->assertFalse($message->hasIdLessThan(10));
    }

    public function testHasIdLessThanReturnsFalseWhenMessageIdIsGreater(): void
    {
        $message = $this->createMessage(id: 10);

        $this->assertFalse($message->hasIdLessThan(9));
        $this->assertFalse($message->hasIdLessThan(1));
        $this->assertFalse($message->hasIdLessThan(-10));
    }

    public function testHasIdGreaterThanReturnsTrueWhenMessageIdIsGreater(): void
    {
        $message = $this->createMessage(id: 10);

        $this->assertTrue($message->hasIdGreaterThan(5));
        $this->assertTrue($message->hasIdGreaterThan(9));
        $this->assertTrue($message->hasIdGreaterThan(-100));
    }

    public function testHasIdGreaterThanReturnsFalseWhenMessageIdIsEqual(): void
    {
        $message = $this->createMessage(id: 10);

        $this->assertFalse($message->hasIdGreaterThan(10));
    }

    public function testHasIdGreaterThanReturnsFalseWhenMessageIdIsLess(): void
    {
        $message = $this->createMessage(id: 10);

        $this->assertFalse($message->hasIdGreaterThan(15));
        $this->assertFalse($message->hasIdGreaterThan(100));
    }
}

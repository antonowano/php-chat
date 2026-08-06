<?php

namespace Tests\Antonowano\Chat;

use DateTime;
use PHPUnit\Framework\TestCase;
use Antonowano\Chat\Message;

class MessageTest extends TestCase
{
    private function createMessage(int $id = 0, string $datetime = 'now'): Message
    {
        return new Message(
            id: $id,
            text: 'Text message',
            createdAt: new DateTime($datetime),
            author: 'Ivan',
        );
    }

    public function testToString()
    {
        $message = $this->createMessage(id: 15432, datetime: '2026-08-05 21:22:13');

        $this->assertSame('[21:22:13 05.08.2026] [#15432] Ivan: Text message', (string) $message);
    }
}

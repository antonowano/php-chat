<?php

namespace Tests\Antonowano\Chat;

use DateTime;
use PHPUnit\Framework\TestCase;
use Antonowano\Chat\Message;

class MessageTest extends TestCase
{
    private function createMessage(string $datetime): Message
    {
        return new Message(
            text: 'Text message',
            createdAt: new DateTime($datetime),
            author: 'Ivan',
        );
    }

    public function testToString()
    {
        $message = $this->createMessage('2026-08-05 21:22:13');

        $this->assertSame('[21:22:13 05.08.2026] Ivan: Text message', (string) $message);
    }
}

<?php

namespace Tests\Antonowano\Chat;

use DateTime;
use PHPUnit\Framework\TestCase;
use Antonowano\Chat\Message;

class MessageTest extends TestCase
{
    public function testToString()
    {
        $message = new Message(
            text: 'Text message',
            createdAt: new DateTime('2026-08-05 21:22:13'),
            author: 'Ivan',
        );
        $this->assertSame('[21:22:13 05.08.2026] Ivan: Text message', $message->toString());
    }
}

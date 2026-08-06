<?php

namespace Tests\Antonowano\Chat;

use Antonowano\Chat\Chat;
use Antonowano\Chat\Message;
use DateTime;
use PHPUnit\Framework\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    protected function createMessage(int $id = 0, string $datetime = 'now'): Message
    {
        return new Message(
            id: $id,
            text: 'Text message',
            createdAt: new DateTime($datetime),
            author: 'User',
        );
    }

    protected function createChat(): Chat
    {
        return new Chat();
    }
}

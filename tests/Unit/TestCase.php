<?php

namespace Tests\Antonowano\Chat\Unit;

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

    /**
     * @param list<int> $ids
     * @return list<Message>
     */
    protected function createMessages(array $ids): array
    {
        return array_map(fn ($id) => $this->createMessage(id: $id), $ids);
    }

    protected function fillChatWithMessages(Chat $chat, array $messages = []): void
    {
        foreach ($messages as $message) {
            $chat->sendMessage($message);
        }
    }
}

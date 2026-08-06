<?php

namespace Tests\Antonowano\Chat;

use Antonowano\Chat\Message;
use DateTime;
use PHPUnit\Framework\TestCase;
use Antonowano\Chat\Chat;

class ChatTest extends TestCase
{
    private function createMessage(): Message
    {
        return new Message(
            id: 0,
            text: 'Text message',
            createdAt: new DateTime('now'),
            author: 'User',
        );
    }

    public function testEmptyChat()
    {
        $chat = new Chat();
        $this->assertEquals([], $chat->getLastMessages(10));
    }

    public function testSendMessageAddsMessageToChat()
    {
        $message = $this->createMessage();

        $chat = new Chat();
        $chat->sendMessage($message);
        $this->assertEquals([$message], $chat->getLastMessages(5));
    }

    public function testGetLastMessagesReturnsCorrectNumberOfRecentMessages()
    {
        $message1 = $this->createMessage();
        $message2 = $this->createMessage();
        $message3 = $this->createMessage();

        $chat = new Chat();
        $chat->sendMessage($message1);
        $chat->sendMessage($message2);
        $chat->sendMessage($message3);

        $this->assertEquals([$message2, $message3], $chat->getLastMessages(2));
        $this->assertEquals([$message1, $message2, $message3], $chat->getLastMessages(5));
    }
}

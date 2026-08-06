<?php

namespace Tests\Antonowano\Chat;

use Antonowano\Chat\Message;
use DateTime;
use PHPUnit\Framework\TestCase;
use Antonowano\Chat\Chat;

class ChatTest extends TestCase
{
    private function createMessage(int $id = 0): Message
    {
        return new Message(
            id: $id,
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

    public function testGetMessagesAfterIdReturnsMessagesWithGreaterId()
    {
        $message1 = $this->createMessage(id: 1);
        $message2 = $this->createMessage(id: 2);
        $message3 = $this->createMessage(id: 3);

        $chat = new Chat();
        $chat->sendMessage($message1);
        $chat->sendMessage($message2);
        $chat->sendMessage($message3);

        $this->assertSame([], $chat->getMessagesAfterId(3, 5));
        $this->assertSame([$message3], $chat->getMessagesAfterId(2, 5));
        $this->assertSame([$message2, $message3], $chat->getMessagesAfterId(1, 5));
    }

    public function testGetMessagesAfterIdRespectsCountLimit()
    {
        $message1 = $this->createMessage(id: 1);
        $message2 = $this->createMessage(id: 2);
        $message3 = $this->createMessage(id: 3);
        $message4 = $this->createMessage(id: 4);
        $message5 = $this->createMessage(id: 5);

        $chat = new Chat();
        $chat->sendMessage($message1);
        $chat->sendMessage($message2);
        $chat->sendMessage($message3);
        $chat->sendMessage($message4);
        $chat->sendMessage($message5);

        $this->assertSame([$message2, $message3], $chat->getMessagesAfterId(1, 2));
        $this->assertSame([$message2, $message3, $message4], $chat->getMessagesAfterId(1, 3));
        $this->assertSame([], $chat->getMessagesAfterId(3, 0));
        $this->assertSame([], $chat->getMessagesAfterId(5, 10));
    }

    public function testGetMessagesAfterIdRespectsCountWithNonSequentialIds()
    {
        $message1 = $this->createMessage(id: 10);
        $message2 = $this->createMessage(id: 20);
        $message3 = $this->createMessage(id: 30);
        $message4 = $this->createMessage(id: 40);

        $chat = new Chat();
        $chat->sendMessage($message1);
        $chat->sendMessage($message2);
        $chat->sendMessage($message3);
        $chat->sendMessage($message4);

        $this->assertSame([$message2, $message3, $message4], $chat->getMessagesAfterId(15, 5));
    }
}

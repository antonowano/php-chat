<?php

namespace Tests\Antonowano\Chat;

use Antonowano\Chat\Message;
use DateTime;
use PHPUnit\Framework\TestCase;
use Antonowano\Chat\Chat;

class ChatTest extends TestCase
{
    private function messageFromIvan(string $text, string $datetime = 'now'): Message
    {
        return new Message($text, new DateTime($datetime), 'Ivan');
    }

    private function messageFromOlga(string $text, string $datetime = 'now'): Message
    {
        return new Message($text, new DateTime($datetime), 'Olga');
    }

    public function testEmptyChat()
    {
        $chat = new Chat();
        $this->assertEquals([], $chat->getLastMessages(10));
    }

    public function testSendMessageAddsMessageToChat()
    {
        $message = $this->messageFromOlga('Hello, World!');

        $chat = new Chat();
        $chat->sendMessage($message);
        $this->assertEquals([$message], $chat->getLastMessages(5));
    }

    public function testGetLastMessagesReturnsCorrectNumberOfRecentMessages()
    {
        $message1 = $this->messageFromIvan('Hey Olga, are you free for a call later?');
        $message2 = $this->messageFromOlga('Hi Ivan! Sure, what time works for you?');
        $message3 = $this->messageFromIvan('Great! Let\'s do 5 PM, if that\'s okay.');

        $chat = new Chat();
        $chat->sendMessage($message1);
        $chat->sendMessage($message2);
        $chat->sendMessage($message3);
        $this->assertEquals([$message2, $message3], $chat->getLastMessages(2));
        $this->assertEquals([$message1, $message2, $message3], $chat->getLastMessages(5));
    }

    public function testGetMessagesByDateTimeReturnsEmptyWhenNoMessagesAfterDate()
    {
        $chat = new Chat();
        $chat->sendMessage($this->messageFromIvan('Hello', '2026-08-06 10:00:00'));

        $this->assertEmpty(
            $chat->getMessagesByDateTime(new DateTime('2026-08-07 00:00:00'), 5)
        );
    }

    public function testGetMessagesByDateTimeRespectsLimit()
    {
        $message1 = $this->messageFromIvan('First', '2026-08-06 10:00:00');
        $message2 = $this->messageFromOlga('Second', '2026-08-06 10:01:00');
        $message3 = $this->messageFromIvan('Third', '2026-08-06 10:02:00');

        $chat = new Chat();
        $chat->sendMessage($message1);
        $chat->sendMessage($message2);
        $chat->sendMessage($message3);

        $this->assertEquals(
            [$message2, $message3],
            $chat->getMessagesByDateTime(new DateTime('2026-08-06 10:00:00'), 2)
        );
    }

    public function testGetMessagesByDateTimeReturnsAllMessagesFromDate()
    {
        $message1 = $this->messageFromIvan('First', '2026-08-06 10:00:00');
        $message2 = $this->messageFromOlga('Second', '2026-08-06 10:01:00');

        $chat = new Chat();
        $chat->sendMessage($message1);
        $chat->sendMessage($message2);

        $this->assertEquals(
            [$message1, $message2],
            $chat->getMessagesByDateTime(new DateTime('2026-08-06 00:00:00'), 10)
        );
    }
}

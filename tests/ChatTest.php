<?php

namespace Tests\Antonowano\Chat;

use Antonowano\Chat\Message;
use DateTime;
use PHPUnit\Framework\TestCase;
use Antonowano\Chat\Chat;

class ChatTest extends TestCase
{
    private function messageFromIvan(string $text): Message
    {
        return new Message($text, new DateTime('now'), 'Ivan');
    }

    private function messageFromOlga(string $text): Message
    {
        return new Message($text, new DateTime('now'), 'Olga');
    }

    public function testEmptyChat()
    {
        $chat = new Chat();
        $this->assertEquals([], $chat->getLastMessages(10));
    }

    public function testSendMessageAddsMessageToChat()
    {
        $message = $this->messageFromIvan('Hello, World!');

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
}

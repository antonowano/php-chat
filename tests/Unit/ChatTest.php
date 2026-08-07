<?php

namespace Tests\Antonowano\Chat\Unit;

use Antonowano\Chat\ArrayChat;
use Antonowano\Chat\ChatInterface;
use PHPUnit\Framework\Attributes\DataProvider;

class ChatTest extends TestCase
{
    public static function chatImplements(): array
    {
        return [
            [new ArrayChat()],
        ];
    }

    #[DataProvider('chatImplements')]
    public function testEmptyChat(ChatInterface $chat): void
    {
        $this->assertEquals([], $chat->getLastMessages(10));
    }

    #[DataProvider('chatImplements')]
    public function testSendMessageAddsMessageToChat(ChatInterface $chat): void
    {
        $messages = $this->createMessages(ids: [1]);
        $this->fillChatWithMessages($chat, $messages);

        $this->assertEquals($messages, $chat->getLastMessages(5));
    }

    #[DataProvider('chatImplements')]
    public function testGetLastMessagesReturnsCorrectNumberOfRecentMessages(ChatInterface $chat): void
    {
        $messages = $this->createMessages(ids: [1, 2, 3]);
        $this->fillChatWithMessages($chat, $messages);
        [$m1, $m2, $m3] = $messages;

        $this->assertEquals([$m2, $m3], $chat->getLastMessages(2));
        $this->assertEquals([$m1, $m2, $m3], $chat->getLastMessages(5));
    }

    #[DataProvider('chatImplements')]
    public function testGetMessagesAfterIdReturnsOnlyMessagesWithGreaterId(ChatInterface $chat): void
    {
        $messages = $this->createMessages(ids: [1, 2, 3]);
        $this->fillChatWithMessages($chat, $messages);
        [, $m2, $m3] = $messages;

        $this->assertSame([$m2, $m3], $chat->getMessagesAfterId(1, 10));
        $this->assertSame([$m3], $chat->getMessagesAfterId(2, 10));
        $this->assertSame([], $chat->getMessagesAfterId(3, 10));
    }

    #[DataProvider('chatImplements')]
    public function testGetMessagesAfterIdRespectsCountLimit(ChatInterface $chat): void
    {
        $messages = $this->createMessages(ids: [1, 2, 3, 4, 5]);
        $this->fillChatWithMessages($chat, $messages);
        [, $m2, $m3, $m4, ] = $messages;

        $this->assertSame([$m2, $m3], $chat->getMessagesAfterId(1, 2));
        $this->assertSame([$m2, $m3, $m4], $chat->getMessagesAfterId(1, 3));
    }

    #[DataProvider('chatImplements')]
    public function testGetMessagesAfterIdWithZeroCountReturnsEmpty(ChatInterface $chat): void
    {
        $messages = $this->createMessages(ids: [1, 2, 3]);
        $this->fillChatWithMessages($chat, $messages);

        $this->assertSame([], $chat->getMessagesAfterId(0, 0));
        $this->assertSame([], $chat->getMessagesAfterId(1, 0));
    }

    #[DataProvider('chatImplements')]
    public function testGetMessagesAfterIdReturnsAllAvailableWhenCountExceedsTotal(ChatInterface $chat): void
    {
        $messages = $this->createMessages(ids: [1, 2, 3]);
        $this->fillChatWithMessages($chat, $messages);

        $result = $chat->getMessagesAfterId(0, 100);

        $this->assertSame($messages, $result);
    }

    #[DataProvider('chatImplements')]
    public function testGetMessagesAfterIdWorksWithNonSequentialIds(ChatInterface $chat): void
    {
        $messages = $this->createMessages(ids: [10, 20, 30, 40]);
        $this->fillChatWithMessages($chat, $messages);
        [, $m2, $m3, ] = $messages;

        $result = $chat->getMessagesAfterId(15, 2);

        $this->assertSame([$m2, $m3], $result);
    }

    #[DataProvider('chatImplements')]
    public function testGetMessagesAfterIdWithNegativeIdReturnsAllMessages(ChatInterface $chat): void
    {
        $messages = $this->createMessages(ids: [1, 2, 3]);
        $this->fillChatWithMessages($chat, $messages);

        $this->assertSame($messages, $chat->getMessagesAfterId(-1, 10));
    }
}

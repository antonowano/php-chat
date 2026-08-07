<?php

namespace Tests\Antonowano\Chat\Unit;

use Antonowano\Chat\Chat;

class ChatTest extends TestCase
{
    private Chat $chat;

    protected function setUp(): void
    {
        parent::setUp();
        $this->chat = new Chat();
    }

    public function testEmptyChat(): void
    {
        $this->assertEquals([], $this->chat->getLastMessages(10));
    }

    public function testSendMessageAddsMessageToChat(): void
    {
        $messages = $this->createMessages(ids: [1]);
        $this->fillChatWithMessages($this->chat, $messages);

        $this->assertEquals($messages, $this->chat->getLastMessages(5));
    }

    public function testGetLastMessagesReturnsCorrectNumberOfRecentMessages(): void
    {
        $messages = $this->createMessages(ids: [1, 2, 3]);
        $this->fillChatWithMessages($this->chat, $messages);
        [$m1, $m2, $m3] = $messages;

        $this->assertEquals([$m2, $m3], $this->chat->getLastMessages(2));
        $this->assertEquals([$m1, $m2, $m3], $this->chat->getLastMessages(5));
    }

    public function testGetMessagesAfterIdReturnsOnlyMessagesWithGreaterId(): void
    {
        $messages = $this->createMessages(ids: [1, 2, 3]);
        $this->fillChatWithMessages($this->chat, $messages);
        [, $m2, $m3] = $messages;

        $this->assertSame([$m2, $m3], $this->chat->getMessagesAfterId(1, 10));
        $this->assertSame([$m3], $this->chat->getMessagesAfterId(2, 10));
        $this->assertSame([], $this->chat->getMessagesAfterId(3, 10));
    }

    public function testGetMessagesAfterIdRespectsCountLimit(): void
    {
        $messages = $this->createMessages(ids: [1, 2, 3, 4, 5]);
        $this->fillChatWithMessages($this->chat, $messages);
        [, $m2, $m3, $m4, ] = $messages;

        $this->assertSame([$m2, $m3], $this->chat->getMessagesAfterId(1, 2));
        $this->assertSame([$m2, $m3, $m4], $this->chat->getMessagesAfterId(1, 3));
    }

    public function testGetMessagesAfterIdWithZeroCountReturnsEmpty(): void
    {
        $messages = $this->createMessages(ids: [1, 2, 3]);
        $this->fillChatWithMessages($this->chat, $messages);

        $this->assertSame([], $this->chat->getMessagesAfterId(0, 0));
        $this->assertSame([], $this->chat->getMessagesAfterId(1, 0));
    }

    public function testGetMessagesAfterIdReturnsAllAvailableWhenCountExceedsTotal(): void
    {
        $messages = $this->createMessages(ids: [1, 2, 3]);
        $this->fillChatWithMessages($this->chat, $messages);

        $result = $this->chat->getMessagesAfterId(0, 100);

        $this->assertSame($messages, $result);
    }

    public function testGetMessagesAfterIdWorksWithNonSequentialIds(): void
    {
        $messages = $this->createMessages(ids: [10, 20, 30, 40]);
        $this->fillChatWithMessages($this->chat, $messages);
        [, $m2, $m3, ] = $messages;

        $result = $this->chat->getMessagesAfterId(15, 2);

        $this->assertSame([$m2, $m3], $result);
    }

    public function testGetMessagesAfterIdWithNegativeIdReturnsAllMessages(): void
    {
        $messages = $this->createMessages(ids: [1, 2, 3]);
        $this->fillChatWithMessages($this->chat, $messages);

        $this->assertSame($messages, $this->chat->getMessagesAfterId(-1, 10));
    }
}

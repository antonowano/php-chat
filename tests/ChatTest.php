<?php

namespace Tests\Antonowano\Chat;

class ChatTest extends TestCase
{
    public function testEmptyChat()
    {
        $chat = $this->createChat();
        $this->assertEquals([], $chat->getLastMessages(10));
    }

    public function testSendMessageAddsMessageToChat()
    {
        $messages = $this->createMessages(ids: [1]);
        $chat = $this->createChat($messages);

        $this->assertEquals($messages, $chat->getLastMessages(5));
    }

    public function testGetLastMessagesReturnsCorrectNumberOfRecentMessages()
    {
        $messages = $this->createMessages(ids: [1, 2, 3]);
        $chat = $this->createChat($messages);
        [$m1, $m2, $m3] = $messages;

        $this->assertEquals([$m2, $m3], $chat->getLastMessages(2));
        $this->assertEquals([$m1, $m2, $m3], $chat->getLastMessages(5));
    }

    public function testGetMessagesAfterIdReturnsOnlyMessagesWithGreaterId(): void
    {
        $messages = $this->createMessages(ids: [1, 2, 3]);
        $chat = $this->createChat($messages);
        [, $m2, $m3] = $messages;

        $this->assertSame([$m2, $m3], $chat->getMessagesAfterId(1, 10));
        $this->assertSame([$m3], $chat->getMessagesAfterId(2, 10));
        $this->assertSame([], $chat->getMessagesAfterId(3, 10));
    }

    public function testGetMessagesAfterIdRespectsCountLimit(): void
    {
        $messages = $this->createMessages(ids: [1, 2, 3, 4, 5]);
        $chat = $this->createChat($messages);
        [, $m2, $m3, $m4, ] = $messages;

        $this->assertSame([$m2, $m3], $chat->getMessagesAfterId(1, 2));
        $this->assertSame([$m2, $m3, $m4], $chat->getMessagesAfterId(1, 3));
    }

    public function testGetMessagesAfterIdWithZeroCountReturnsEmpty(): void
    {
        $messages = $this->createMessages(ids: [1, 2, 3]);
        $chat = $this->createChat($messages);

        $this->assertSame([], $chat->getMessagesAfterId(0, 0));
        $this->assertSame([], $chat->getMessagesAfterId(1, 0));
    }

    public function testGetMessagesAfterIdReturnsAllAvailableWhenCountExceedsTotal(): void
    {
        $messages = $this->createMessages(ids: [1, 2, 3]);
        $chat = $this->createChat($messages);

        $result = $chat->getMessagesAfterId(0, 100);

        $this->assertSame($messages, $result);
    }

    public function testGetMessagesAfterIdWorksWithNonSequentialIds(): void
    {
        $messages = $this->createMessages(ids: [10, 20, 30, 40]);
        $chat = $this->createChat($messages);
        [, $m2, $m3, ] = $messages;

        $result = $chat->getMessagesAfterId(15, 2);

        $this->assertSame([$m2, $m3], $result);
    }

    public function testGetMessagesAfterIdWithNegativeIdReturnsAllMessages(): void
    {
        $messages = $this->createMessages(ids: [1, 2, 3]);
        $chat = $this->createChat($messages);

        $this->assertSame($messages, $chat->getMessagesAfterId(-1, 10));
    }
}

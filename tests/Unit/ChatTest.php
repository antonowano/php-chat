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

        $this->assertObjectListEquals($messages, $this->chat->getLastMessages(5));
    }

    public function testGetLastMessagesReturnsCorrectNumberOfRecentMessages(): void
    {
        $messages = $this->createMessages(ids: [1, 2, 3]);
        $this->fillChatWithMessages($this->chat, $messages);
        [$m1, $m2, $m3] = $messages;

        $this->assertObjectListEquals([$m2, $m3], $this->chat->getLastMessages(2));
        $this->assertObjectListEquals([$m1, $m2, $m3], $this->chat->getLastMessages(5));
    }

    public function testGetMessagesBeforeIdReturnsMessagesWithLessId(): void
    {
        $messages = $this->createMessages(ids: [1, 2, 3]);
        $this->fillChatWithMessages($this->chat, $messages);
        [$m1, $m2, ] = $messages;

        $this->assertObjectListEquals([$m1, $m2], $this->chat->getMessagesBeforeId(3, 10));
        $this->assertObjectListEquals([$m1], $this->chat->getMessagesBeforeId(2, 10));
        $this->assertObjectListEquals([], $this->chat->getMessagesBeforeId(1, 10));
    }

    public function testGetMessagesBeforeIdRespectsCountLimit(): void
    {
        $messages = $this->createMessages(ids: [1, 2, 3, 4, 5]);
        $this->fillChatWithMessages($this->chat, $messages);
        [, $m2, $m3, $m4, ] = $messages;

        $this->assertObjectListEquals([$m2, $m3], $this->chat->getMessagesBeforeId(4, 2));
        $this->assertObjectListEquals([$m2, $m3, $m4], $this->chat->getMessagesBeforeId(5, 3));
    }

    public function testGetMessagesAfterIdReturnsOnlyMessagesWithGreaterId(): void
    {
        $messages = $this->createMessages(ids: [1, 2, 3]);
        $this->fillChatWithMessages($this->chat, $messages);
        [, $m2, $m3] = $messages;

        $this->assertObjectListEquals([$m2, $m3], $this->chat->getMessagesAfterId(1, 10));
        $this->assertObjectListEquals([$m3], $this->chat->getMessagesAfterId(2, 10));
        $this->assertObjectListEquals([], $this->chat->getMessagesAfterId(3, 10));
    }

    public function testGetMessagesAfterIdRespectsCountLimit(): void
    {
        $messages = $this->createMessages(ids: [1, 2, 3, 4, 5]);
        $this->fillChatWithMessages($this->chat, $messages);
        [, $m2, $m3, $m4, ] = $messages;

        $this->assertObjectListEquals([$m2, $m3], $this->chat->getMessagesAfterId(1, 2));
        $this->assertObjectListEquals([$m2, $m3, $m4], $this->chat->getMessagesAfterId(1, 3));
    }

    public function testGetMessagesAfterIdWithZeroCountReturnsEmpty(): void
    {
        $messages = $this->createMessages(ids: [1, 2, 3]);
        $this->fillChatWithMessages($this->chat, $messages);

        $this->assertObjectListEquals([], $this->chat->getMessagesAfterId(0, 0));
        $this->assertObjectListEquals([], $this->chat->getMessagesAfterId(1, 0));
    }

    public function testGetMessagesAfterIdReturnsAllAvailableWhenCountExceedsTotal(): void
    {
        $messages = $this->createMessages(ids: [1, 2, 3]);
        $this->fillChatWithMessages($this->chat, $messages);

        $result = $this->chat->getMessagesAfterId(0, 100);

        $this->assertObjectListEquals($messages, $result);
    }

    public function testGetMessagesAfterIdWorksWithNonSequentialIds(): void
    {
        $messages = $this->createMessages(ids: [10, 20, 30, 40]);
        $this->fillChatWithMessages($this->chat, $messages);
        [, $m2, $m3, ] = $messages;

        $result = $this->chat->getMessagesAfterId(15, 2);

        $this->assertObjectListEquals([$m2, $m3], $result);
    }

    public function testGetMessagesAfterIdWithNegativeIdReturnsAllMessages(): void
    {
        $messages = $this->createMessages(ids: [1, 2, 3]);
        $this->fillChatWithMessages($this->chat, $messages);

        $this->assertObjectListEquals($messages, $this->chat->getMessagesAfterId(-1, 10));
    }
}

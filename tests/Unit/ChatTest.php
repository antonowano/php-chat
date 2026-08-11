<?php

namespace Tests\Antonowano\Chat\Unit;

class ChatTest extends TestCase
{
    public function testEmptyChat(): void
    {
        $chat = $this->createChat();
        $this->assertEquals([], $chat->getLastMessages(10));
    }

    public function testSendMessageAddsMessageToChat(): void
    {
        $messages = $this->createMessages(ids: [1]);
        $chat = $this->createChat($messages);

        $this->assertObjectListEquals($messages, $chat->getLastMessages(5));
    }

    public function testGetLastMessagesReturnsCorrectNumberOfRecentMessages(): void
    {
        $messages = $this->createMessages(ids: [1, 2, 3]);
        $chat = $this->createChat($messages);
        [$m1, $m2, $m3] = $messages;

        $this->assertObjectListEquals([$m2, $m3], $chat->getLastMessages(2));
        $this->assertObjectListEquals([$m1, $m2, $m3], $chat->getLastMessages(5));
    }

    public function testGetMessagesBeforeIdReturnsMessagesWithLessId(): void
    {
        $messages = $this->createMessages(ids: [1, 2, 3]);
        $chat = $this->createChat($messages);
        [$m1, $m2, ] = $messages;

        $this->assertObjectListEquals([$m1, $m2], $chat->getMessagesBeforeId(3, 10));
        $this->assertObjectListEquals([$m1], $chat->getMessagesBeforeId(2, 10));
        $this->assertObjectListEquals([], $chat->getMessagesBeforeId(1, 10));
    }

    public function testGetMessagesBeforeIdRespectsCountLimit(): void
    {
        $messages = $this->createMessages(ids: [1, 2, 3, 4, 5]);
        $chat = $this->createChat($messages);
        [, $m2, $m3, $m4, ] = $messages;

        $this->assertObjectListEquals([$m2, $m3], $chat->getMessagesBeforeId(4, 2));
        $this->assertObjectListEquals([$m2, $m3, $m4], $chat->getMessagesBeforeId(5, 3));
    }

    public function testGetMessagesAfterIdReturnsOnlyMessagesWithGreaterId(): void
    {
        $messages = $this->createMessages(ids: [1, 2, 3]);
        $chat = $this->createChat($messages);
        [, $m2, $m3] = $messages;

        $this->assertObjectListEquals([$m2, $m3], $chat->getMessagesAfterId(1, 10));
        $this->assertObjectListEquals([$m3], $chat->getMessagesAfterId(2, 10));
        $this->assertObjectListEquals([], $chat->getMessagesAfterId(3, 10));
    }

    public function testGetMessagesAfterIdRespectsCountLimit(): void
    {
        $messages = $this->createMessages(ids: [1, 2, 3, 4, 5]);
        $chat = $this->createChat($messages);
        [, $m2, $m3, $m4, ] = $messages;

        $this->assertObjectListEquals([$m2, $m3], $chat->getMessagesAfterId(1, 2));
        $this->assertObjectListEquals([$m2, $m3, $m4], $chat->getMessagesAfterId(1, 3));
    }

    public function testGetMessagesAfterIdWithZeroCountReturnsEmpty(): void
    {
        $messages = $this->createMessages(ids: [1, 2, 3]);
        $chat = $this->createChat($messages);

        $this->assertObjectListEquals([], $chat->getMessagesAfterId(0, 0));
        $this->assertObjectListEquals([], $chat->getMessagesAfterId(1, 0));
    }

    public function testGetMessagesAfterIdReturnsAllAvailableWhenCountExceedsTotal(): void
    {
        $messages = $this->createMessages(ids: [1, 2, 3]);
        $chat = $this->createChat($messages);

        $result = $chat->getMessagesAfterId(0, 100);

        $this->assertObjectListEquals($messages, $result);
    }

    public function testGetMessagesAfterIdWorksWithNonSequentialIds(): void
    {
        $messages = $this->createMessages(ids: [10, 20, 30, 40]);
        $chat = $this->createChat($messages);
        [, $m2, $m3, ] = $messages;

        $result = $chat->getMessagesAfterId(15, 2);

        $this->assertObjectListEquals([$m2, $m3], $result);
    }

    public function testGetMessagesAfterIdWithNegativeIdReturnsAllMessages(): void
    {
        $messages = $this->createMessages(ids: [1, 2, 3]);
        $chat = $this->createChat($messages);

        $this->assertObjectListEquals($messages, $chat->getMessagesAfterId(-1, 10));
    }
}

<?php

namespace Tests\Antonowano\Chat\Unit;

use Antonowano\Chat\ChatListener;
use Antonowano\Chat\Message;
use Antonowano\Chat\NewMessage;
use Symfony\Component\Clock\MockClock;

class ChatTest extends TestCase
{
    public function testEmptyChat(): void
    {
        $chat = $this->createChat();
        $this->assertEquals([], $chat->getLastMessages(10));
    }

    public function testSendMessageAddsMessageToChat(): void
    {
        $clock = new MockClock('now');
        $chat = $this->createChat([], $clock);
        $chat->sendMessage(new NewMessage('First', 'Ivan'));
        $chat->sendMessage(new NewMessage('Second', 'Olga'));

        $messages = $chat->getLastMessages(5);

        $this->assertObjectEquals(
            $this->createMessage(id: 1, text: 'First', createdAt: $clock->now(), author: 'Ivan'),
            $messages[0]
        );
        $this->assertObjectEquals(
            $this->createMessage(id: 2, text: 'Second', createdAt: $clock->now(), author: 'Olga'),
            $messages[1]
        );
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

    public function testCallMessageSentWhenTheMessageIsSent(): void
    {
        $clock = new MockClock('now');
        $listener = $this->createMock(ChatListener::class);
        $listener->expects($this->once())->method('onMessageSent')->with(
            $this->callback(function (Message $message) use ($clock) {
                $expected = $this->createMessage(id: 1, text: 'First', createdAt: $clock->now(), author: 'Ivan');
                return $message->equals($expected);
            }),
        );
        $chat = $this->createChat([], $clock);
        $chat->addListener($listener);
        $chat->sendMessage(new NewMessage('First', 'Ivan'));
    }

    public function testDoNotCallMessageSentIfListenerIsRemoved(): void
    {
        $chat = $this->createChat();
        $listener = $this->createMock(ChatListener::class);
        $listener->expects($this->once())->method('id')->willReturn('fd1');
        $listener->expects($this->never())->method('onMessageSent');
        $chat->addListener($listener);
        $chat->removeListenerById('fd1');
        $chat->sendMessage(new NewMessage('First', 'Ivan'));
    }

    public function testCallMessageSentIfListenerIsNotRemoved(): void
    {
        $chat = $this->createChat();
        $listener = $this->createMock(ChatListener::class);
        $listener->expects($this->once())->method('id')->willReturn('fd1');
        $listener->expects($this->once())->method('onMessageSent');
        $chat->addListener($listener);
        $chat->removeListenerById('not_found');
        $chat->sendMessage(new NewMessage('First', 'Ivan'));
    }
}

<?php

namespace Tests\Antonowano\Chat\Unit;

use Antonowano\Chat\Chat;
use Antonowano\Chat\Message;
use Antonowano\Chat\NewMessage;
use DateTime;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Psr\Clock\ClockInterface;
use Symfony\Component\Clock\NativeClock;

class TestCase extends BaseTestCase
{
    protected function createMessage(
        int $id = 0,
        string $text = 'Text message',
        ?\DateTimeInterface $createdAt = null,
        string $author = 'User',
    ): Message {
        return new Message(
            id: $id,
            text: $text,
            createdAt: $createdAt ?? new DateTime('now'),
            author: $author,
        );
    }

    /**
     * @param list<int> $ids
     * @return list<Message>
     */
    protected function createMessages(array $ids): array
    {
        return array_map(fn ($id) => $this->createMessage(id: $id), $ids);
    }

    protected function createChat(array $messages = [], ?ClockInterface $clock = null): Chat
    {
        return new Chat(
            clock: $clock ?? new NativeClock(),
            messages: $messages,
        );
    }

    protected function createNewMessage(string $text, string $author): NewMessage
    {
        return new NewMessage(
            text: $text,
            author: $author,
        );
    }

    protected function messageTexts(): array
    {
        return [
            ['text' => 'Hi! How was your exam today?', 'author' => 'Ivan'],
            ['text' => 'Hard! I think I failed the last part.', 'author' => 'Olga'],
            ['text' => 'Oh no! Want to grab some coffee?', 'author' => 'Ivan'],
            ['text' => 'Sure! I really need a break now.', 'author' => 'Olga'],
            ['text' => 'Great! See you at 5 pm then.', 'author' => 'Ivan'],
        ];
    }

    /**
     * @return list<Message>
     */
    protected function fillChat(Chat $chat, ClockInterface $clock): array
    {
        $messages = [];

        foreach ($this->messageTexts() as $i => $message) {
            $chat->sendMessage($this->createNewMessage($message['text'], $message['author']));
            $messages[] = $this->createMessage($i + 1, $message['text'], $clock->now(), $message['author']);
        }

        return $messages;
    }

    /**
     * @param list<object> $expected
     * @param list<object> $actual
     */
    protected function assertObjectListEquals(array $expected, array $actual): void
    {
        $this->assertCount(
            count($expected),
            $actual,
            'Array sizes do not match'
        );

        foreach ($expected as $index => $expectedObject) {
            $this->assertObjectEquals(
                $expectedObject,
                $actual[$index],
                'equals',
                "Objects at index {$index} are not equal"
            );
        }
    }
}

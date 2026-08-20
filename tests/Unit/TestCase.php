<?php

namespace Tests\Antonowano\Chat\Unit;

use Antonowano\Chat\Chat;
use Antonowano\Chat\Message;
use Antonowano\Chat\NewMessage;
use DateTime;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Psr\Clock\ClockInterface;

class TestCase extends BaseTestCase
{
    protected function createMessage(
        int $id = 0,
        string $text = 'Text message',
        ?\DateTimeInterface $createdAt = null,
        string $author = 'User',
        int $chatId = 1,
    ): Message {
        return new Message(
            chatId: $chatId,
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

    protected function createNewMessage(int $chatId, string $text, string $author): NewMessage
    {
        return new NewMessage(
            chatId: $chatId,
            text: $text,
            author: $author,
        );
    }

    protected function messageTexts(): array
    {
        return [
            ['chatId' => 2, 'text' => 'Hello, World!', 'author' => 'John Doe'],
            ['chatId' => 1, 'text' => 'Hi! How was your exam today?', 'author' => 'Ivan'],
            ['chatId' => 1, 'text' => 'Hard! I think I failed the last part.', 'author' => 'Olga'],
            ['chatId' => 1, 'text' => 'Oh no! Want to grab some coffee?', 'author' => 'Ivan'],
            ['chatId' => 1, 'text' => 'Sure! I really need a break now.', 'author' => 'Olga'],
            ['chatId' => 1, 'text' => 'Great! See you at 5 pm then.', 'author' => 'Ivan'],
            ['chatId' => 2, 'text' => 'Good buy, World!', 'author' => 'John Doe'],
        ];
    }

    /**
     * @return list<Message>
     */
    protected function fillChat(Chat $chat, ClockInterface $clock): array
    {
        $messages = [];

        foreach ($this->messageTexts() as $i => $message) {
            $chat->sendMessage($this->createNewMessage($message['chatId'], $message['text'], $message['author']));
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

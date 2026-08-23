<?php

namespace Tests\Antonowano\Chat\Unit;

use Antonowano\Chat\Message;
use Antonowano\Chat\MessageStorage;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Psr\Clock\ClockInterface;

class TestCase extends BaseTestCase
{
    protected function messageTexts(): array
    {
        $john = createUser(name: 'John Doe');
        $olga = createUser(name: 'Olga');
        $ivan = createUser(name: 'Ivan');

        return [
            ['roomId' => 2, 'text' => 'Hello, World!', 'author' => $john],
            ['roomId' => 1, 'text' => 'Hi! How was your exam today?', 'author' => $ivan],
            ['roomId' => 1, 'text' => 'Hard! I think I failed the last part.', 'author' => $olga],
            ['roomId' => 1, 'text' => 'Oh no! Want to grab some coffee?', 'author' => $ivan],
            ['roomId' => 1, 'text' => 'Sure! I really need a break now.', 'author' => $olga],
            ['roomId' => 1, 'text' => 'Great! See you at 5 pm then.', 'author' => $ivan],
            ['roomId' => 2, 'text' => 'Good buy, World!', 'author' => $john],
        ];
    }

    /**
     * @return list<Message>
     */
    protected function fillChat(MessageStorage $messageStorage, ClockInterface $clock): array
    {
        $messages = [];

        foreach ($this->messageTexts() as $i => $message) {
            $messageStorage->create(createNewMessage($message['roomId'], $message['text'], $message['author']));
            $messages[] = createMessage($i + 1, $message['text'], $clock->now(), $message['roomId'], $message['author']);
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

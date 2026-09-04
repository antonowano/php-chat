<?php

namespace Antonowano\Chat;

use Psr\Clock\ClockInterface;

class MessageStorage
{
    /** @var array<int, Message> */
    private array $messages = [];

    private int $autoIncrement = 1;

    public function __construct(
        private readonly ClockInterface $clock,
    ) {
    }

    public function create(NewMessage $newMessage): Message
    {
        $message = new Message(
            room: $newMessage->room(),
            id: $this->autoIncrement++,
            text: $newMessage->text(),
            createdAt: $this->clock->now(),
            author: $newMessage->author(),
        );
        $this->messages[$newMessage->room()->id()][] = $message;
        return $message;
    }

    /**
     * @return list<Message>
     */
    public function getLastMessages(int $roomId, int $count): array
    {
        return array_slice($this->messages[$roomId] ?? [], -$count);
    }

    /**
     * @return list<Message>
     */
    public function getMessagesBeforeId(int $roomId, int $id, int $count): array
    {
        $messages = array_values(array_filter(
            $this->messages[$roomId] ?? [],
            static fn (Message $message) => $message->hasIdLessThan($id)
        ));

        return array_slice($messages, -$count);
    }

    /**
     * @return list<Message>
     */
    public function getMessagesAfterId(int $roomId, int $id, int $count): array
    {
        $messages = array_values(array_filter(
            $this->messages[$roomId] ?? [],
            static fn (Message $message) => $message->hasIdGreaterThan($id)
        ));

        return array_slice($messages, 0, $count);
    }
}

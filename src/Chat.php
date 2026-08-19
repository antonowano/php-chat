<?php

namespace Antonowano\Chat;

use Psr\Clock\ClockInterface;

class Chat
{
    /** @var array<string, ChatListener> */
    private array $listeners = [];

    private int $autoIncrement;

    public function __construct(
        private readonly ClockInterface $clock,
        /** @var list<Message> */
        private array $messages = [],
    ) {
        $this->autoIncrement = count($this->messages) + 1;
    }

    public function sendMessage(NewMessage $newMessage): void
    {
        $message = new Message(
            id: $this->autoIncrement++,
            text: $newMessage->text(),
            createdAt: $this->clock->now(),
            author: $newMessage->author(),
        );
        $this->messages[] = $message;

        foreach ($this->listeners as $listener) {
            $listener->onMessageSent($message);
        }
    }

    /**
     * @return list<Message>
     */
    public function getLastMessages(int $count): array
    {
        return array_slice($this->messages, -$count);
    }

    /**
     * @return list<Message>
     */
    public function getMessagesBeforeId(int $id, int $count): array
    {
        $messages = array_values(array_filter(
            $this->messages,
            static fn (Message $message) => $message->hasIdLessThan($id)
        ));

        return array_slice($messages, -$count);
    }

    /**
     * @return list<Message>
     */
    public function getMessagesAfterId(int $id, int $count): array
    {
        $messages = array_values(array_filter(
            $this->messages,
            static fn (Message $message) => $message->hasIdGreaterThan($id)
        ));

        return array_slice($messages, 0, $count);
    }

    public function addListener(string $id, ChatListener $listener): void
    {
        $this->listeners[$id] = $listener;
    }

    public function removeListenerById(string $id): void
    {
        unset($this->listeners[$id]);
    }
}

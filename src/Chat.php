<?php

namespace Antonowano\Chat;

use Psr\Clock\ClockInterface;

class Chat
{
    /** @var array<string, ChatListener> */
    private array $listeners = [];

    private array $messages = [];

    private int $autoIncrement = 1;

    public function __construct(
        private readonly ClockInterface $clock,
    ) {
    }

    public function sendMessage(NewMessage $newMessage): void
    {
        $message = new Message(
            chatId: $newMessage->chatId(),
            id: $this->autoIncrement++,
            text: $newMessage->text(),
            createdAt: $this->clock->now(),
            author: $newMessage->author(),
        );
        $this->messages[$newMessage->chatId()][] = $message;

        foreach ($this->listeners as $listener) {
            $listener->onMessageSent($message);
        }
    }

    /**
     * @return list<Message>
     */
    public function getLastMessages(int $chatId, int $count): array
    {
        return array_slice($this->messages[$chatId] ?? [], -$count);
    }

    /**
     * @return list<Message>
     */
    public function getMessagesBeforeId(int $chatId, int $id, int $count): array
    {
        $messages = array_values(array_filter(
            $this->messages[$chatId] ?? [],
            static fn (Message $message) => $message->hasIdLessThan($id)
        ));

        return array_slice($messages, -$count);
    }

    /**
     * @return list<Message>
     */
    public function getMessagesAfterId(int $chatId, int $id, int $count): array
    {
        $messages = array_values(array_filter(
            $this->messages[$chatId] ?? [],
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

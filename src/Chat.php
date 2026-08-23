<?php

namespace Antonowano\Chat;

class Chat
{
    /** @var array<string, ChatListener> */
    private array $listeners = [];

    public function __construct(
        private readonly MessageStorage $messageStorage,
    ) {
    }

    public function sendMessage(NewMessage $newMessage): void
    {
        $message = $this->messageStorage->create($newMessage);

        foreach ($this->listeners as $listener) {
            $listener->onMessageSent($message);
        }
    }

    /**
     * @return list<Message>
     */
    public function getLastMessages(int $roomId, int $count): array
    {
        return $this->messageStorage->getLastMessages($roomId, $count);
    }

    /**
     * @return list<Message>
     */
    public function getMessagesBeforeId(int $roomId, int $id, int $count): array
    {
        return $this->messageStorage->getMessagesBeforeId($roomId, $id, $count);
    }

    /**
     * @return list<Message>
     */
    public function getMessagesAfterId(int $roomId, int $id, int $count): array
    {
        return $this->messageStorage->getMessagesAfterId($roomId, $id, $count);
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

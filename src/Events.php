<?php

namespace Antonowano\Chat;

class Events
{
    /** @var array<string, ChatListener> */
    private array $listeners = [];

    public function messageSent(Message $message): void
    {
        foreach ($this->listeners as $listener) {
            $listener->onMessageSent($message);
        }
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

<?php

namespace Antonowano\Chat;

class Chat
{
    /** @var list<Message> */
    private array $messages = [];

    public function sendMessage(Message $message): void
    {
        $this->messages[] = $message;
    }

    /**
     * @return list<Message>
     */
    public function getLastMessages(int $count): array
    {
        return array_slice($this->messages, -$count);
    }
}

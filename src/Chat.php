<?php

namespace Antonowano\Chat;

use DateTimeInterface;

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

    /**
     * @return list<Message>
     */
    public function getMessagesByDateTime(DateTimeInterface $dateTime, int $count): array
    {
        $filtered = array_values(array_filter(
            $this->messages,
            static fn (Message $message): bool => $message->isCreatedAfter($dateTime),
        ));

        return array_slice($filtered, -$count);
    }
}

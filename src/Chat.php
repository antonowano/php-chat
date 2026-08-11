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
}

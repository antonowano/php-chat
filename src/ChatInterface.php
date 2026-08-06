<?php

namespace Antonowano\Chat;

interface ChatInterface
{
    public function sendMessage(Message $message): void;

    /**
     * @return list<Message>
     */
    public function getLastMessages(int $count): array;

    /**
     * @return list<Message>
     */
    public function getMessagesAfterId(int $id, int $count): array;
}
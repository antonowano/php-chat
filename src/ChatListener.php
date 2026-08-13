<?php

namespace Antonowano\Chat;

interface ChatListener
{
    public function id(): string;

    public function onMessageSent(Message $message): void;
}

<?php

namespace Antonowano\Chat;

interface ChatListener
{
    public function onMessageSent(Message $message): void;
}

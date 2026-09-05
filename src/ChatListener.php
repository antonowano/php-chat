<?php

namespace Antonowano\Chat;

interface ChatListener
{
    public function onMessageSent(Message $message): void;

    public function onRoomCreated(Room $room): void;

    public function onUserRemovedFromRoom(User $user, Room $room): void;

    public function onRemovedRoom(Room $room): void;
}

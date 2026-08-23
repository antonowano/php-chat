<?php

namespace Antonowano\Chat;

class RoomStorage
{
    /** @var array<int, Room> */
    private array $rooms = [];

    private int $autoIncrement = 1;

    public function create(NewRoom $room): Room
    {
        $id = $this->autoIncrement++;
        $room = new Room(
            id: $id,
            memberIds: $room->memberIds(),
        );
        $this->rooms[$id] = $room;
        return $room;
    }

    public function findById(int $id): ?Room
    {
        return $this->rooms[$id] ?? null;
    }
}

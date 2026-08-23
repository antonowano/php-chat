<?php

namespace Antonowano\Chat;

class RoomStorage
{
    /** @var array<int, Room> */
    private array $rooms = [];

    private int $autoIncrement = 1;

    public function create(NewRoom $room): void
    {
        $id = $this->autoIncrement++;
        $room = new Room(
            id: $id,
            memberIds: $room->memberIds(),
        );
        $this->rooms[$id] = $room;
    }

    public function findById(int $id): ?Room
    {
        return $this->rooms[$id] ?? null;
    }
}

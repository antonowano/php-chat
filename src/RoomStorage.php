<?php

namespace Antonowano\Chat;

class RoomStorage
{
    /** @var array<int, Room> */
    private array $rooms = [];

    private int $autoIncrement = 1;

    public function __construct(
        private readonly UserStorage $userStorage,
    ) {
    }

    public function create(NewRoom $newRoom): Room
    {
        $id = $this->autoIncrement++;
        $room = new Room(
            id: $id,
            members: $this->userStorage->findAllById($newRoom->memberIds()),
        );
        $this->rooms[$id] = $room;
        return $room;
    }

    public function findById(int $id): ?Room
    {
        return $this->rooms[$id] ?? null;
    }
}

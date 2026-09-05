<?php

namespace Antonowano\Chat;

class RoomStorage
{
    /** @var array<int, Room> */
    private array $rooms = [];

    private int $autoIncrement = 1;

    public function create(NewRoom $newRoom): Room
    {
        $id = $this->autoIncrement++;
        $room = new Room(
            id: $id,
            members: $newRoom->members(),
        );
        $this->rooms[$id] = $room;
        return $room;
    }

    public function findById(int $id): ?Room
    {
        return $this->rooms[$id] ?? null;
    }

    /**
     * @return list<Room>
     */
    public function findAllForUser(User $user, int $offset, int $limit): array
    {
        $rooms = [];

        foreach ($this->rooms as $room) {
            if ($room->hasMember($user)) {
                $rooms[] = $room;
            }
        }

        return array_slice($rooms, $offset, $limit);
    }

    public function save(Room $room): void
    {
        $this->rooms[$room->id()] = $room;
    }

    public function remove(Room $room): void
    {
        unset($this->rooms[$room->id()]);
    }

    /**
     * @return list<Room>
     */
    public function findAllByMember(User $user): array
    {
        $rooms = [];

        foreach ($this->rooms as $room) {
            if ($room->hasMember($user)) {
                $rooms[] = $room;
            }
        }

        return $rooms;
    }
}

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

    public function removeMemberFromAllRooms(User $user): void
    {
        foreach ($this->rooms as $room) {
            if ($room->hasMember($user)) {
                $changedRoom = $room->removeMember($user);
                $this->save($changedRoom);
            }
        }
    }
}

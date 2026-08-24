<?php

namespace Antonowano\Chat;

readonly class Room
{
    public function __construct(
        private int $id,
        /** @var list<User> $members */
        private array $members,
    ) {
    }

    public function equals(Room $room): bool
    {
        return $this->id === $room->id;
    }

    public function id(): int
    {
        return $this->id;
    }

    public function hasMember(User $user): bool
    {
        return array_any($this->members, fn($member) => $member->equals($user));
    }

    public function toChatPayload(): array
    {
        return [
            'id' => $this->id,
            'members' => array_map(fn (User $member) => $member->toChatPayload(), $this->members),
        ];
    }
}

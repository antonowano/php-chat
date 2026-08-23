<?php

namespace Antonowano\Chat;

readonly class Room
{
    public function __construct(
        private int $id,
        /** @var list<int> $memberIds */
        private array $memberIds,
    ) {
    }

    public function id(): int
    {
        return $this->id;
    }

    public function hasMember(User $user): bool
    {
        return in_array($user->id(), $this->memberIds);
    }
}

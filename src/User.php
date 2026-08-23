<?php

namespace Antonowano\Chat;

readonly class User
{
    public function __construct(
        private int $id,
        private string $name,
        private Role $role,
    ) {
    }

    public function equals(User $message): bool
    {
        return $this->name === $message->name
            && $this->role === $message->role
            && $this->id === $message->id;
    }

    public function id(): int
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function role(): Role
    {
        return $this->role;
    }
}

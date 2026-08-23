<?php

namespace Antonowano\Chat;

readonly class NewUser
{
    public function __construct(
        private string $name,
        private Role $role,
    ) {
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

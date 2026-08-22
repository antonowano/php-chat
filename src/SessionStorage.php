<?php

namespace Antonowano\Chat;

class SessionStorage
{
    /** @var array<int, User> */
    private array $sessions;

    public function add(int $key, User $user): void
    {
        $this->sessions[$key] = $user;
    }

    public function remove(int $key): void
    {
        unset($this->sessions[$key]);
    }

    public function get(int $key): User
    {
        return $this->sessions[$key];
    }
}
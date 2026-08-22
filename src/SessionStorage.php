<?php

namespace Antonowano\Chat;

class SessionStorage
{
    private array $sessions;

    public function add(int $key, string $user): void
    {
        $this->sessions[$key] = $user;
    }

    public function remove(int $key): void
    {
        unset($this->sessions[$key]);
    }

    public function get(int $key): string
    {
        return $this->sessions[$key];
    }
}
<?php

namespace Antonowano\Chat;

class UserStorage
{
    /** @var array<string, User> */
    private array $usersByTokens = [];

    private int $autoIncrement = 1;

    public function register(NewUser $user): string
    {
        $token = uniqid();
        $this->usersByTokens[$token] = new User(
            id: $this->autoIncrement++,
            name: $user->name(),
            role: $user->role(),
        );
        return $token;
    }

    public function findByToken(?string $token): ?User
    {
        if (empty($token)) {
            return null;
        }
        return $this->usersByTokens[$token] ?? null;
    }
}

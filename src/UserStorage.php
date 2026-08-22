<?php

namespace Antonowano\Chat;

class UserStorage
{
    /** @var array<string, User> */
    private array $usersByTokens = [];

    public function register(User $user): string
    {
        $token = uniqid();
        $this->usersByTokens[$token] = $user;
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

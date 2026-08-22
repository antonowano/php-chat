<?php

namespace Antonowano\Chat;

class UserStorage
{
    private array $namesByTokens = [];

    public function register(string $name): string
    {
        $token = uniqid();
        $this->namesByTokens[$token] = $name;
        return $token;
    }

    public function findNameByToken(?string $token): ?string
    {
        if (empty($token)) {
            return null;
        }
        return $this->namesByTokens[$token] ?? null;
    }
}

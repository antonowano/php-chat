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

    public function findNameByToken(string $token): ?string
    {
        return $this->namesByTokens[$token] ?? null;
    }
}

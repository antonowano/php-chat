<?php

namespace Antonowano\Chat;

readonly class User
{
    public function __construct(
        private int $id,
        private string $name,
        private Role $role,
        private string $accessToken,
    ) {
    }

    public function equals(User $message): bool
    {
        return $this->name === $message->name
            && $this->role === $message->role
            && $this->accessToken === $message->accessToken
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

    public function accessToken(): string
    {
        return $this->accessToken;
    }

    public function toChatPayload(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }
}

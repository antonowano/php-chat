<?php

namespace Antonowano\Chat;

use Ramsey\Uuid\Uuid;

class UserStorage
{
    /** @var array<string, User> */
    private array $usersByTokens = [];

    private int $autoIncrement = 1;

    public function create(NewUser $user): User
    {
        $token = Uuid::uuid4()->toString();
        $user = new User(
            id: $this->autoIncrement++,
            name: $user->name(),
            role: $user->role(),
            accessToken: $token,
        );
        $this->usersByTokens[$token] = $user;
        return $user;
    }

    public function findByToken(?string $token): ?User
    {
        if (empty($token)) {
            return null;
        }
        return $this->usersByTokens[$token] ?? null;
    }

    public function findById(int $id): ?User
    {
        $users = $this->findAllById([$id]);
        return $users[0] ?? null;
    }

    /**
     * @param list<int> $ids
     * @return list<User>
     */
    public function findAllById(array $ids): array
    {
        $result = [];
        foreach ($this->usersByTokens as $user) {
            if (in_array($user->id(), $ids)) {
                $result[] = $user;
            }
        }
        return $result;
    }

    public function remove(User $user): void
    {
        foreach ($this->usersByTokens as $token => $u) {
            if ($u->equals($user)) {
                unset($this->usersByTokens[$token]);
            }
        }
    }
}

<?php

namespace Antonowano\Chat;

readonly class NewRoom
{
    public function __construct(
        /** @var list<User> */
        private array $members,
    ) {
    }

    /**
     * @return list<User>
     */
    public function members(): array
    {
        return $this->members;
    }
}

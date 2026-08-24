<?php

namespace Antonowano\Chat;

readonly class NewRoom
{
    public function __construct(
        /** @var list<int> */
        private array $memberIds,
    ) {
    }

    /**
     * @return list<int>
     */
    public function memberIds(): array
    {
        return $this->memberIds;
    }
}

<?php

namespace Antonowano\Chat;

readonly class NewRoom
{
    public function __construct(
        private array $memberIds,
    ) {
    }

    public function memberIds(): array
    {
        return $this->memberIds;
    }
}

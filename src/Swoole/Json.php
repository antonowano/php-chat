<?php

namespace Antonowano\Chat\Swoole;

readonly class Json
{
    public function __construct(
        private array $data,
    ) {
    }

    public static function create(string $json): self
    {
        return new self(json_decode($json, true));
    }

    public function equals(Json $json): bool
    {
        return $this->data === $json->data;
    }

    public function get(string $key, ?string $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }
}

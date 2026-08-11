<?php

namespace Antonowano\Chat\Swoole;

readonly class DataBag
{
    public function __construct(
        private array $data,
    ) {
    }

    public static function fromJson(string $json): self
    {
        return new self(json_decode($json, true));
    }

    public static function fromQuery(string $query): self
    {
        parse_str($query, $data);
        return new self($data);
    }

    public function equals(DataBag $json): bool
    {
        return $this->data === $json->data;
    }

    public function get(string $key, ?string $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }
}

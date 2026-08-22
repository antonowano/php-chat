<?php

namespace Antonowano\Chat\Api;

interface HttpRequest
{
    public function path(): string;

    public function httpMethod(): string;

    public function content(): string;

    public function queryString(): string;

    public function header(string $name): ?string;
}

<?php

namespace Antonowano\Chat\Stream;

interface WsResponse
{
    public function push(array $data): void;
}

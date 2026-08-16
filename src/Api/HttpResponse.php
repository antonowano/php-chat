<?php

namespace Antonowano\Chat\Api;

use Antonowano\Chat\Enums\HttpStatusCode;

interface HttpResponse
{
    public function json(array $data, HttpStatusCode $status): void;
}

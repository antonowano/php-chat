<?php

namespace Antonowano\Chat\Api;

interface HttpResponse
{
    public function json(array $data, HttpStatusCode $status): void;
}

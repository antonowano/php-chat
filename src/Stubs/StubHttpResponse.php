<?php

namespace Antonowano\Chat\Stubs;

use Antonowano\Chat\Api\HttpResponse;
use Antonowano\Chat\Enums\HttpStatusCode;

class StubHttpResponse implements HttpResponse
{
    private HttpStatusCode $statusCode;
    private array $data;

    public function json(array $data, HttpStatusCode $status): void
    {
        $this->data = $data;
        $this->statusCode = $status;
    }

    public function statusCode(): HttpStatusCode
    {
        return $this->statusCode;
    }

    public function data(): array
    {
        return $this->data;
    }
}
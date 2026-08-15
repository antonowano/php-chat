<?php

namespace Antonowano\Chat;

interface HttpResponse
{
    public function json(array $data, HttpStatusCode $status): void;
}

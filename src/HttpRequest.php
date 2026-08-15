<?php

namespace Antonowano\Chat;

interface HttpRequest
{
    public function path(): string;

    public function httpMethod(): string;

    public function content(): string;

    public function queryString(): string;
}

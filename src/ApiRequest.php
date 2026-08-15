<?php

namespace Antonowano\Chat;

readonly class ApiRequest
{
    public function __construct(
        private HttpRequest $httpRequest,
    ) {
    }

    public function routeMatches(string $path, string $method): bool
    {
        return $this->httpRequest->path() === $path
            && $this->httpRequest->httpMethod() === strtoupper($method);
    }

    public function json(): DataBag
    {
        return DataBag::fromJson($this->httpRequest->content());
    }

    public function query(): DataBag
    {
        return DataBag::fromQuery($this->httpRequest->queryString());
    }
}

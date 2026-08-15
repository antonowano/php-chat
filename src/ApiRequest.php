<?php

namespace Antonowano\Chat;

readonly class ApiRequest
{
    public function __construct(
        private HttpRequest $httpRequest,
    ) {
    }

    public function routeMatches(HttpPath $path, HttpMethod $method): bool
    {
        return $this->httpRequest->path() === $path->value
            && $this->httpRequest->httpMethod() === $method->value;
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

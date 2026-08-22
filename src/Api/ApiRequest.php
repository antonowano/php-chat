<?php

namespace Antonowano\Chat\Api;

use Antonowano\Chat\DataBag;

readonly class ApiRequest
{
    public function __construct(
        private HttpRequest $httpRequest,
    ) {
    }

    public function routeMatches(ApiRoute $route): bool
    {
        return $this->httpRequest->path() === $route->path()
            && $this->httpRequest->httpMethod() === $route->method();
    }

    public function json(): DataBag
    {
        return DataBag::fromJson($this->httpRequest->content());
    }

    public function query(): DataBag
    {
        return DataBag::fromQuery($this->httpRequest->queryString());
    }

    public function accessToken(): ?string
    {
        $authentication = $this->httpRequest->header('Authorization');
        if (empty($authentication)) {
            return null;
        }
        return str_replace('Bearer ', '', $authentication);
    }
}

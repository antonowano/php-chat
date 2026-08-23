<?php

namespace Antonowano\Chat\Api;

use Antonowano\Chat\DataBag;
use Antonowano\Chat\User;

readonly class ApiRequest
{
    public function __construct(
        private HttpRequest $httpRequest,
        private User $user,
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

    public function user(): User
    {
        return $this->user;
    }
}

<?php

namespace Antonowano\Chat\Enums;

enum HttpStatusCode: int
{
    case OK = 200;
    case CREATED = 201;
    case FORBIDDEN = 403;
    case NOT_FOUND = 404;
}

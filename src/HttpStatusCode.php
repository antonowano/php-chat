<?php

namespace Antonowano\Chat;

enum HttpStatusCode: int
{
    case OK = 200;
    case CREATED = 201;
    case NOT_FOUND = 404;
}

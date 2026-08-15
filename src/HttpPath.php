<?php

namespace Antonowano\Chat;

enum HttpPath: string
{
    case SEND_MESSAGE = '/api/message/send';
    case LAST_MESSAGES = '/api/messages/last';
    case NEXT_MESSAGES = '/api/messages/next';
    case PREVIOUS_MESSAGES = '/api/messages/previous';
}

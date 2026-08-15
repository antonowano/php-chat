<?php

namespace Antonowano\Chat\Swoole;

use Antonowano\Chat\HttpContentType;
use Antonowano\Chat\HttpHeader;
use Antonowano\Chat\HttpResponse;
use Antonowano\Chat\HttpStatusCode;
use OpenSwoole\Http\Response;

readonly class SwooleHttpResponse implements HttpResponse
{
    public function __construct(
        private Response $swooleResponse,
    ) {
    }

    public function json(array $data, HttpStatusCode $status): void
    {
        $this->swooleResponse->header(HttpHeader::CONTENT_TYPE->value, HttpContentType::JSON->value);
        $this->swooleResponse->status($status->value);
        $this->swooleResponse->end(json_encode($data));
    }
}

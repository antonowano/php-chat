<?php

namespace Antonowano\Chat\Swoole;

use Antonowano\Chat\Api\HttpResponse;
use Antonowano\Chat\Enums\HttpContentType;
use Antonowano\Chat\Enums\HttpHeader;
use Antonowano\Chat\Enums\HttpStatusCode;
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

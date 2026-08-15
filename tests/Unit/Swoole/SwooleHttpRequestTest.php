<?php

namespace Tests\Antonowano\Chat\Unit\Swoole;

use Antonowano\Chat\HttpMethod;
use Antonowano\Chat\Swoole\SwooleHttpRequest;
use OpenSwoole\Http\Request;
use Tests\Antonowano\Chat\Unit\TestCase;

class SwooleHttpRequestTest extends TestCase
{
    private Request $swooleRequest;
    private SwooleHttpRequest $request;

    protected function setUp(): void
    {
        parent::setUp();
        $this->swooleRequest = $this->createMock(Request::class);
        $this->request = new SwooleHttpRequest($this->swooleRequest);
    }

    public function testPath(): void
    {
        $path = '/api/chat/123';
        $this->swooleRequest->server['path_info'] = $path;
        $this->assertSame($path, $this->request->path());
    }

    public function testHttpMethod(): void
    {
        $method = HttpMethod::GET->value;
        $this->swooleRequest->method('getMethod')->willReturn($method);
        $this->assertSame($method, $this->request->httpMethod());
    }

    public function testContent(): void
    {
        $content = 'Hello World!';
        $this->swooleRequest->method('getContent')->willReturn($content);
        $this->assertSame($content, $this->request->content());
    }

    public function testQueryString(): void
    {
        $queryString = 'limit=20&offset=0';
        $this->swooleRequest->server['query_string'] = $queryString;
        $this->assertSame($queryString, $this->request->queryString());
    }
}

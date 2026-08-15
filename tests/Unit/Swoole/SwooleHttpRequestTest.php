<?php

namespace Tests\Antonowano\Chat\Unit\Swoole;

use Antonowano\Chat\Swoole\DataBag;
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

    public function testIsPathReturnsTrueWhenRouteMatches(): void
    {
        $this->swooleRequest->server['path_info'] = '/api/messages/last';

        $this->assertTrue($this->request->isPath('/api/messages/last'));
        $this->assertFalse($this->request->isPath('/api/messages/send'));
    }

    public function testIsMethodReturnsGetWhenGetRequest(): void
    {
        $this->swooleRequest->method('getMethod')->willReturn('GET');

        $this->assertTrue($this->request->isMethod('GET'));
        $this->assertFalse($this->request->isMethod('POST'));
    }

    public function testJsonReturnsValueByKey(): void
    {
        $data = [
            'name' => 'John Doe',
            'text' => 'Hello World',
        ];
        $this->swooleRequest->method('getContent')->willReturn(json_encode($data));

        $this->assertObjectEquals(new DataBag($data), $this->request->json());
    }

    public function testQueryReturnsValueByKey(): void
    {
        $data = [
            'id' => '123',
            'limit' => '30',
        ];
        $this->swooleRequest->server['query_string'] = http_build_query($data);

        $this->assertObjectEquals(new DataBag($data), $this->request->query());
    }
}

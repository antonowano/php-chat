<?php

namespace Tests\Antonowano\Chat\Unit\Swoole;

use Antonowano\Chat\Swoole\ApiRequest;
use Tests\Antonowano\Chat\Unit\TestCase;

class ApiRequestTest extends TestCase
{
    /** @var \OpenSwoole\Http\Request  */
    private object $swooleRequest;
    private ApiRequest $apiRequest;

    protected function setUp(): void
    {
        parent::setUp();
        $this->swooleRequest = $this->createMock('OpenSwoole\Http\Request');
        $this->apiRequest = new ApiRequest($this->swooleRequest);
    }

    public function testIsPathReturnsTrueWhenRouteMatches(): void
    {
        $this->swooleRequest->server['path_info'] = '/api/messages/last';

        $this->assertTrue($this->apiRequest->isPath('/api/messages/last'));
    }

    public function testIsPathReturnsFalseWhenRouteNotMatches(): void
    {
        $this->swooleRequest->server['path_info'] = '/api/message/send';

        $this->assertFalse($this->apiRequest->isPath('/api/messages/last'));
    }

    public function testIsPathReturnsFalseWhenRoutePartiallyMatches(): void
    {
        $this->swooleRequest->server['path_info'] = '/api/message/send';

        $this->assertFalse($this->apiRequest->isPath('/api/message'));
    }

    public function testIsPathReturnsFalseWhenRoutePartiallyMatches2(): void
    {
        $this->swooleRequest->server['path_info'] = '/api/message';

        $this->assertFalse($this->apiRequest->isPath('/api/message/send'));
    }

    public function testIsMethodReturnsGetWhenGetRequest(): void
    {
        $this->swooleRequest->method('getMethod')->willReturn('GET');

        $this->assertTrue($this->apiRequest->isMethod('GET'));
    }

    public function testIsMethodReturnsPostWhenGetRequest(): void
    {
        $this->swooleRequest->method('getMethod')->willReturn('POST');

        $this->assertTrue($this->apiRequest->isMethod('POST'));
    }

    public function testJsonReturnsValueByKey(): void
    {
        $data = [
            'name' => 'John Doe',
            'text' => 'Hello World',
        ];
        $this->swooleRequest->method('getContent')->willReturn(json_encode($data));

        $this->assertEquals($data['name'], $this->apiRequest->json()->get('name'));
    }

    public function testQueryReturnsValueByKey(): void
    {
        $this->swooleRequest->server['query_string'] = 'id=123&limit=30';

        $this->assertSame('30', $this->apiRequest->query()->get('limit'));
    }
}

<?php

namespace Tests\Antonowano\Chat\Unit;

use Antonowano\Chat\ApiRequest;
use Antonowano\Chat\DataBag;
use Antonowano\Chat\HttpMethod;
use Antonowano\Chat\HttpPath;
use Antonowano\Chat\HttpRequest;

class ApiRequestTest extends TestCase
{
    private HttpRequest $httpRequest;
    private ApiRequest $request;

    protected function setUp(): void
    {
        parent::setUp();
        $this->httpRequest = $this->createMock(HttpRequest::class);
        $this->request = new ApiRequest($this->httpRequest);
    }

    public function testIsRouteMatches(): void
    {
        $this->httpRequest->expects($this->once())->method('path')
            ->willReturn(HttpPath::SEND_MESSAGE->value);
        $this->httpRequest->expects($this->once())->method('httpMethod')
            ->willReturn(HttpMethod::POST->value);
        $this->assertTrue($this->request->routeMatches(HttpPath::SEND_MESSAGE, HttpMethod::POST));
    }

    public function testJsonReturnsDataBag(): void
    {
        $data = [
            'name' => 'John Doe',
            'text' => 'Hello World',
        ];
        $this->httpRequest->expects($this->once())->method('content')
            ->willReturn(json_encode($data));

        $this->assertObjectEquals(new DataBag($data), $this->request->json());
    }

    public function testQueryReturnsDataBag(): void
    {
        $data = [
            'id' => '123',
            'limit' => '30',
        ];
        $this->httpRequest->expects($this->once())->method('queryString')
            ->willReturn(http_build_query($data));

        $this->assertObjectEquals(new DataBag($data), $this->request->query());
    }
}

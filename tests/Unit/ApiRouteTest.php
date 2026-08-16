<?php

namespace Tests\Antonowano\Chat\Unit;

use Antonowano\Chat\ApiRoute;

class ApiRouteTest extends TestCase
{
    public function testApiRoute(): void
    {
        $route = new ApiRoute('GET', '/api/profile', [$this, 'testApiRoute']);
        $this->assertSame('GET', $route->method());
        $this->assertSame('/api/profile', $route->path());
        $this->assertIsCallable($route->callback());
    }
}

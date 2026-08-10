<?php

namespace Tests\Antonowano\Chat\Unit\Swoole;

use Antonowano\Chat\Swoole\Json;
use Tests\Antonowano\Chat\Unit\TestCase;

class JsonTest extends TestCase
{
    public function testJsonEquals(): void
    {
        $json1 = Json::create('{"name": "John", "text":   "Hello, World!"}' . PHP_EOL);
        $json2 = Json::create('{"name": "John", "text": "Hello, World!"  }');

        $this->assertObjectEquals($json1, $json2);
    }

    public function testJsonNotEquals(): void
    {
        $json1 = Json::create('{"name": "John", "text": "Hello, World!"}');
        $json2 = Json::create('{"name": "John2", "text": "Hello, World!"}');

        $this->assertObjectNotEquals($json1, $json2);
    }

    public function testGetReturnsValueFromData(): void
    {
        $json = Json::create('{"name": "John", "text": "Hello, World!"}');

        $this->assertSame('John', $json->get('name'));
    }

    public function testGetReturnsDefaultValueWhenKeyNotPresentInData(): void
    {
        $json = Json::create('{"text": "Hello, World!"}');

        $this->assertSame('Ivan', $json->get('name', 'Ivan'));
    }
}

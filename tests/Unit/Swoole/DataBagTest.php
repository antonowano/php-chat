<?php

namespace Tests\Antonowano\Chat\Unit\Swoole;

use Antonowano\Chat\Swoole\DataBag;
use Tests\Antonowano\Chat\Unit\TestCase;

class DataBagTest extends TestCase
{
    public function testJsonEquals(): void
    {
        $json1 = DataBag::fromJson('{"name": "John", "text":   "Hello, World!"}' . PHP_EOL);
        $json2 = DataBag::fromJson('{"name": "John", "text": "Hello, World!"  }');

        $this->assertObjectEquals($json1, $json2);
    }

    public function testJsonNotEquals(): void
    {
        $json1 = DataBag::fromJson('{"name": "John", "text": "Hello, World!"}');
        $json2 = DataBag::fromJson('{"name": "John2", "text": "Hello, World!"}');

        $this->assertObjectNotEquals($json1, $json2);
    }

    public function testGetReturnsValueFromData(): void
    {
        $json = DataBag::fromJson('{"name": "John", "text": "Hello, World!"}');

        $this->assertSame('John', $json->get('name'));
    }

    public function testGetReturnsDefaultValueWhenKeyNotPresentInData(): void
    {
        $json = DataBag::fromJson('{"text": "Hello, World!"}');

        $this->assertSame('Ivan', $json->get('name', 'Ivan'));
    }

    public function testGetFromQueryReturnsValueFromData(): void
    {
        $json = DataBag::fromQuery('id=84303&limit=30');

        $this->assertSame('84303', $json->get('id'));
        $this->assertSame('30', $json->get('limit'));
    }

    public function testGetFromQueryReturnsDefaultValueWhenKeyNotPresentInData(): void
    {
        $json = DataBag::fromQuery('id=84303');

        $this->assertSame('30', $json->get('limit', 30));
    }

    public function testNestedGet(): void
    {
        $data = new DataBag([
            'newMessage' => [
                'text' => 'Hello, World!',
                'author' => 'John',
            ]
        ]);

        $this->assertSame('Hello, World!', $data->get('newMessage.text'));
        $this->assertSame('John', $data->get('newMessage.author'));
    }
}

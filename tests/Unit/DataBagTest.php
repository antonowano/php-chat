<?php

use Antonowano\Chat\DataBag;

test('json equals', function (): void {
    $json1 = DataBag::fromJson('{"name": "John", "text":   "Hello, World!"}' . PHP_EOL);
    $json2 = DataBag::fromJson('{"name": "John", "text": "Hello, World!"  }');

    $this->assertObjectEquals($json1, $json2);
});

test('json not equals', function (): void {
    $json1 = DataBag::fromJson('{"name": "John", "text": "Hello, World!"}');
    $json2 = DataBag::fromJson('{"name": "John2", "text": "Hello, World!"}');

    $this->assertObjectNotEquals($json1, $json2);
});

test('get returns value from data', function (): void {
    $json = DataBag::fromJson('{"name": "John", "text": "Hello, World!"}');

    expect($json->get('name'))->toBe('John');
});

test('get returns default value when key not present in data', function (): void {
    $json = DataBag::fromJson('{"text": "Hello, World!"}');

    expect($json->get('name', 'Ivan'))->toBe('Ivan');
});

test('get from query returns value from data', function (): void {
    $json = DataBag::fromQuery('id=84303&limit=30');

    expect($json->get('id'))->toBe('84303')
        ->and($json->get('limit'))->toBe('30');
});

test('get from query returns default value when key not present in data', function (): void {
    $json = DataBag::fromQuery('id=84303');

    expect($json->get('limit', 30))->toBe('30');
});

test('nested get', function (): void {
    $data = new DataBag([
        'newMessage' => [
            'text' => 'Hello, World!',
            'author' => 'John',
        ]
    ]);

    expect($data->get('newMessage.text'))->toBe('Hello, World!')
        ->and($data->get('newMessage.author'))->toBe('John');
});

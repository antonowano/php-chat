<?php

use Tests\Antonowano\Chat\Unit\TestCase;

uses(TestCase::class);

test('equals', function () {
    $this->assertEquals(
        $this->createNewMessage(1, 'Hello, World!', 'Ivan'),
        $this->createNewMessage(1, 'Hello, World!', 'Ivan')
    );
});

test('not equals', function () {
    $this->assertNotEquals(
        $this->createNewMessage(2, 'Hello, World!1', 'Ivan'),
        $this->createNewMessage(2, 'Hello, World!', 'Ivan')
    );
});

test('not equals2', function () {
    $this->assertNotEquals(
        $this->createNewMessage(3, 'Hello, World!', 'Ivan'),
        $this->createNewMessage(3, 'Hello, World!', 'Olga')
    );
});

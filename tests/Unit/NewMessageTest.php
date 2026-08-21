<?php

use Tests\Antonowano\Chat\Unit\TestCase;

uses(TestCase::class);

it('equals', function (): void {
    $this->assertEquals(
        $this->createNewMessage(1, 'Hello, World!', 'Ivan'),
        $this->createNewMessage(1, 'Hello, World!', 'Ivan')
    );
});

it('not equals', function (): void {
    $this->assertNotEquals(
        $this->createNewMessage(2, 'Hello, World!1', 'Ivan'),
        $this->createNewMessage(2, 'Hello, World!', 'Ivan')
    );
});

it('not equals2', function (): void {
    $this->assertNotEquals(
        $this->createNewMessage(3, 'Hello, World!', 'Ivan'),
        $this->createNewMessage(3, 'Hello, World!', 'Olga')
    );
});

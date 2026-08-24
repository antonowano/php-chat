<?php

it('equals', function (): void {
    $this->assertEquals(
        createNewMessage(1, 'Hello, World!'),
        createNewMessage(1, 'Hello, World!')
    );
});

it('not equals', function (): void {
    $this->assertNotEquals(
        createNewMessage(2, 'Hello, World!1'),
        createNewMessage(2, 'Hello, World!')
    );
});

it('not equals2', function (): void {
    $this->assertNotEquals(
        createNewMessage(3, 'Hello, World!', createUser(name: 'Ivan')),
        createNewMessage(3, 'Hello, World!', createUser(name: 'Olga'))
    );
});

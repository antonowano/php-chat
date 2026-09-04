<?php

it('equals', function (): void {
    $this->assertEquals(
        createNewMessage(createRoom(id: 1), 'Hello, World!'),
        createNewMessage(createRoom(id: 1), 'Hello, World!')
    );
});

it('not equals', function (): void {
    $this->assertNotEquals(
        createNewMessage(createRoom(id: 2), 'Hello, World!1'),
        createNewMessage(createRoom(id: 2), 'Hello, World!')
    );
});

it('not equals2', function (): void {
    $this->assertNotEquals(
        createNewMessage(createRoom(id: 3), 'Hello, World!', createUser(name: 'Ivan')),
        createNewMessage(createRoom(id: 3), 'Hello, World!', createUser(name: 'Olga'))
    );
});

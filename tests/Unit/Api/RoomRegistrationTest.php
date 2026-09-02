<?php

use Antonowano\Chat\Chat;
use Antonowano\Chat\Enums\HttpStatusCode;
use Antonowano\Chat\Role;
use Antonowano\Chat\Stubs\StubHttpRequest;
use Symfony\Component\Clock\MockClock;

beforeEach(function (): void {
    $this->chat = new Chat(new MockClock());
    $this->userStorage = $this->chat->userStorage();
    $this->roomStorage = $this->chat->roomStorage();
    $this->router = $this->chat->apiRouter();
    $user1 = $this->userStorage->create(createNewUser());
    $user2 = $this->userStorage->create(createNewUser());
    $this->request = new StubHttpRequest('POST', '/api/room/register', [], [
        'memberIds' => [$user1->id(), $user2->id()],
    ]);
    $this->registeredRoom = createRoom(
        id: 1,
        members: [$user1, $user2],
    );
});

it('should return 201 Created', function (): void {
    $response = sendRequestToApi($this->router, $this->request, createUser(role: Role::ADMIN));
    expect($response)->statusCode()->toBe(HttpStatusCode::CREATED);
});

it('should return 403 Forbidden for not an admin', function (): void {
    $response = sendRequestToApi($this->router, $this->request, createUser());
    expect($response)->statusCode()->toBe(HttpStatusCode::FORBIDDEN);
});

it('should add the room to the storage', function (): void {
    sendRequestToApi($this->router, $this->request, createUser(role: Role::ADMIN));
    expect($this->roomStorage)->findById(1)->toEqual($this->registeredRoom);
});

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
    $this->user = $this->userStorage->create(createNewUser());
    $this->room = $this->roomStorage->create(createNewRoom(
        members: [$this->user],
    ));
    $this->request = new StubHttpRequest('POST', '/api/user/remove', [], [
        'userId' => $this->user->id(),
    ]);
});

it('can be done by the administrator', function (): void {
    $response = sendRequestToApi($this->router, $this->request, createUser(role: Role::ADMIN));
    $removedUser = $this->userStorage->findById($this->user->id());
    $updatedRoom = $this->roomStorage->findById($this->room->id());
    expect($response)->statusCode()->toBe(HttpStatusCode::OK)
        ->and($removedUser)->toBeNull()
        ->and($updatedRoom)->hasMember($this->user)->toBeFalse();
});

it('cannot be done by the user', function (): void {
    $response = sendRequestToApi($this->router, $this->request, $this->user);
    $removedUser = $this->userStorage->findById($this->user->id());
    $updatedRoom = $this->roomStorage->findById($this->room->id());
    expect($response)->statusCode()->toBe(HttpStatusCode::FORBIDDEN)
        ->and($removedUser)->toBe($this->user)
        ->and($updatedRoom)->hasMember($this->user)->toBeTrue();
});

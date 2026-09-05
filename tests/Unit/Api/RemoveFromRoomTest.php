<?php

use Antonowano\Chat\Chat;
use Antonowano\Chat\ChatListener;
use Antonowano\Chat\Enums\HttpStatusCode;
use Antonowano\Chat\Role;
use Antonowano\Chat\Stubs\StubHttpRequest;
use Symfony\Component\Clock\MockClock;

beforeEach(function (): void {
    $this->chat = new Chat(new MockClock());
    $this->userStorage = $this->chat->userStorage();
    $this->roomStorage = $this->chat->roomStorage();
    $this->router = $this->chat->apiRouter();
    $this->listener = $this->createMock(ChatListener::class);
    $this->chat->events()->addListener('listener1', $this->listener);
    $this->user = $this->userStorage->create(createNewUser());
    $this->room = $this->roomStorage->create(createNewRoom(
        memberIds: [$this->user->id()],
    ));
    $this->request = new StubHttpRequest('POST', '/api/room/remove-user', [], [
        'roomId' => $this->room->id(),
        'userId' => $this->user->id(),
    ]);
});

it('can be done by the administrator', function (): void {
    $this->listener->expects($this->once())->method('onUserRemovedFromRoom');
    $response = sendRequestToApi($this->router, $this->request, createUser(role: Role::ADMIN));
    $updatedRoom = $this->roomStorage->findById($this->room->id());
    expect($response)->statusCode()->toBe(HttpStatusCode::OK)
        ->and($updatedRoom)->hasMember($this->user)->toBeFalse();
});

it('cannot be done by the user', function (): void {
    $this->listener->expects($this->never())->method('onUserRemovedFromRoom');
    $response = sendRequestToApi($this->router, $this->request, $this->user);
    $updatedRoom = $this->roomStorage->findById($this->room->id());
    expect($response)->statusCode()->toBe(HttpStatusCode::FORBIDDEN)
        ->and($updatedRoom)->hasMember($this->user)->toBeTrue();
});

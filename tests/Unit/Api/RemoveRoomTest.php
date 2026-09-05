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
    $this->request = new StubHttpRequest('POST', '/api/room/remove', [], [
        'roomId' => $this->user->id(),
    ]);
});

it('can be done by the administrator', function (): void {
    $this->listener->expects($this->once())->method('onRemovedRoom');
    $response = sendRequestToApi($this->router, $this->request, createUser(role: Role::ADMIN));
    $deletedRoom = $this->roomStorage->findById($this->room->id());
    expect($response)->statusCode()->toBe(HttpStatusCode::OK)
        ->and($deletedRoom)->toBeNull();
});

it('cannot be done by the user', function (): void {
    $this->listener->expects($this->never())->method('onRemovedRoom');
    $response = sendRequestToApi($this->router, $this->request, $this->user);
    $deletedRoom = $this->roomStorage->findById($this->room->id());
    expect($response)->statusCode()->toBe(HttpStatusCode::FORBIDDEN)
        ->and($deletedRoom)->toBe($this->room);
});

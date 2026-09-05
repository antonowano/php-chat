<?php

use Antonowano\Chat\Chat;
use Antonowano\Chat\Enums\HttpStatusCode;
use Antonowano\Chat\Role;
use Antonowano\Chat\Stubs\StubHttpRequest;
use Antonowano\Chat\Stubs\StubWsResponse;
use Antonowano\Chat\Swoole\SwooleWsChatListener;
use Symfony\Component\Clock\MockClock;

beforeEach(function (): void {
    $this->chat = new Chat(new MockClock());
    $this->events = $this->chat->events();
    $this->userStorage = $this->chat->userStorage();
    $this->roomStorage = $this->chat->roomStorage();
    $this->router = $this->chat->apiRouter();
    $this->removingUser = $this->userStorage->create(createNewUser());
    $this->user2 = $this->userStorage->create(createNewUser());
    $this->user3 = $this->userStorage->create(createNewUser());
    $this->room = $this->roomStorage->create(createNewRoom(
        members: [$this->removingUser, $this->user2],
    ));
    $this->userListener = new StubWsResponse();
    $this->userListener2 = new StubWsResponse();
    $this->userListener3 = new StubWsResponse();
    $this->events->addListener('listener1', new SwooleWsChatListener($this->userListener, $this->removingUser));
    $this->events->addListener('listener2', new SwooleWsChatListener($this->userListener2, $this->user2));
    $this->events->addListener('listener3', new SwooleWsChatListener($this->userListener3, $this->user3));
    $this->request = new StubHttpRequest('POST', '/api/room/remove-user', [], [
        'roomId' => $this->room->id(),
        'userId' => $this->removingUser->id(),
    ]);
});

it('can be done by the administrator', function (): void {
    $response = sendRequestToApi($this->router, $this->request, createUser(role: Role::ADMIN));
    $updatedRoom = $this->roomStorage->findById($this->room->id());
    expect($response)->statusCode()->toBe(HttpStatusCode::OK)
        ->and($updatedRoom)->hasMember($this->removingUser)->toBeFalse()
        ->and($this->userListener)->data()->toBe([
            'type' => 'RemovedFromRoom',
            'data' => [
                'userId' => $this->removingUser->id(),
                'roomId' => $this->room->id(),
            ],
        ])
        ->and($this->userListener2)->data()->toBe($this->userListener->data())
        ->and($this->userListener3)->data()->toBe([]);
});

it('cannot be done by the user', function (): void {
    $response = sendRequestToApi($this->router, $this->request, $this->removingUser);
    $updatedRoom = $this->roomStorage->findById($this->room->id());
    expect($response)->statusCode()->toBe(HttpStatusCode::FORBIDDEN)
        ->and($updatedRoom)->hasMember($this->removingUser)->toBeTrue()
        ->and($this->userListener)->data()->toBe([]);
});

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
    $this->user = $this->userStorage->create(createNewUser());
    $this->user2 = $this->userStorage->create(createNewUser());
    $this->room = $this->roomStorage->create(createNewRoom(
        members: [$this->user],
    ));
    $this->userListener = new StubWsResponse();
    $this->userListener2 = new StubWsResponse();
    $this->events->addListener('listener1', new SwooleWsChatListener($this->userListener, $this->user));
    $this->events->addListener('listener2', new SwooleWsChatListener($this->userListener2, $this->user2));
    $this->request = new StubHttpRequest('POST', '/api/room/remove', [], [
        'roomId' => $this->user->id(),
    ]);
});

it('can be done by the administrator', function (): void {
    $response = sendRequestToApi($this->router, $this->request, createUser(role: Role::ADMIN));
    $deletedRoom = $this->roomStorage->findById($this->room->id());
    expect($response)->statusCode()->toBe(HttpStatusCode::OK)
        ->and($deletedRoom)->toBeNull()
        ->and($this->userListener)->data()->toBe([
            'type' => 'RemovedRoom',
            'data' => [
                'roomId' => $this->room->id(),
            ],
        ])
        ->and($this->userListener2)->data()->toBe([]);
});

it('cannot be done by the user', function (): void {
    $response = sendRequestToApi($this->router, $this->request, $this->user);
    $deletedRoom = $this->roomStorage->findById($this->room->id());
    expect($response)->statusCode()->toBe(HttpStatusCode::FORBIDDEN)
        ->and($deletedRoom)->toBe($this->room)
        ->and($this->userListener)->data()->toBe([]);
});

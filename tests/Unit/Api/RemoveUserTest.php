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
    $this->room = $this->roomStorage->create(createNewRoom(
        members: [$this->removingUser],
    ));
    $this->userListener = new StubWsResponse();
    $this->events->addListener('listener1', new SwooleWsChatListener($this->userListener, $this->removingUser));
    $this->request = new StubHttpRequest('POST', '/api/user/remove', [], [
        'userId' => $this->removingUser->id(),
    ]);
});

it('can be done by the administrator', function (): void {
    $response = sendRequestToApi($this->router, $this->request, createUser(role: Role::ADMIN));
    $removedUser = $this->userStorage->findById($this->removingUser->id());
    $updatedRoom = $this->roomStorage->findById($this->room->id());
    expect($response)->statusCode()->toBe(HttpStatusCode::OK)
        ->and($removedUser)->toBeNull()
        ->and($updatedRoom)->hasMember($this->removingUser)->toBeFalse()
        ->and($this->userListener)->data()->toBe([
            'type' => 'RemovedFromRoom',
            'data' => [
                'userId' => $this->removingUser->id(),
                'roomId' => $this->room->id(),
            ],
        ]);
});

it('cannot be done by the user', function (): void {
    $response = sendRequestToApi($this->router, $this->request, $this->removingUser);
    $removedUser = $this->userStorage->findById($this->removingUser->id());
    $updatedRoom = $this->roomStorage->findById($this->room->id());
    expect($response)->statusCode()->toBe(HttpStatusCode::FORBIDDEN)
        ->and($removedUser)->toBe($this->removingUser)
        ->and($updatedRoom)->hasMember($this->removingUser)->toBeTrue();
});

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
    $this->user1 = $this->userStorage->create(createNewUser('Ivan'));
    $this->user2 = $this->userStorage->create(createNewUser('Olga'));
    $this->user3 = $this->userStorage->create(createNewUser());
    $this->userListener1 = new StubWsResponse();
    $this->userListener2 = new StubWsResponse();
    $this->userListener3 = new StubWsResponse();
    $this->events->addListener('listener1', new SwooleWsChatListener($this->userListener1, $this->user1));
    $this->events->addListener('listener2', new SwooleWsChatListener($this->userListener2, $this->user2));
    $this->events->addListener('listener3', new SwooleWsChatListener($this->userListener3, $this->user3));
    $this->request = new StubHttpRequest('POST', '/api/room/register', [], [
        'memberIds' => [$this->user1->id(), $this->user2->id()],
    ]);
});

it('should return 201 Created', function (): void {
    $response = sendRequestToApi($this->router, $this->request, createUser(role: Role::ADMIN));
    expect($response)->statusCode()->toBe(HttpStatusCode::CREATED);
});

it('should notify listeners of a new message', function (): void {
    sendRequestToApi($this->router, $this->request, createUser(role: Role::ADMIN));
    expect($this->userListener1)->data()
        ->toHaveKey('type', 'Room')
        ->toHaveKey('data.id', 1)
        ->toHaveKey('data.members.0.name', 'Ivan')
        ->toHaveKey('data.members.1.name', 'Olga')
        ->and($this->userListener2)->data()->toBe($this->userListener1->data())
        ->and($this->userListener3)->data()->toBe([]);
});

it('should return 403 Forbidden for not an admin', function (): void {
    $response = sendRequestToApi($this->router, $this->request, createUser());
    expect($response)->statusCode()->toBe(HttpStatusCode::FORBIDDEN);
});

it('should add the room to the storage', function (): void {
    sendRequestToApi($this->router, $this->request, createUser(role: Role::ADMIN));
    expect($this->roomStorage->findById(1))->not->toBeNull()
        ->id()->toBe(1)
        ->hasMember($this->user1)->toBeTrue()
        ->hasMember($this->user2)->toBeTrue()
        ->hasMember($this->user3)->toBeFalse();
});

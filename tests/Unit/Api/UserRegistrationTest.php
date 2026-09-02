<?php

use Antonowano\Chat\Chat;
use Antonowano\Chat\Enums\HttpStatusCode;
use Antonowano\Chat\Role;
use Antonowano\Chat\Stubs\StubHttpRequest;
use Symfony\Component\Clock\MockClock;

beforeEach(function (): void {
    $this->chat = new Chat(new MockClock());
    $this->userStorage = $this->chat->userStorage();
    $this->router = $this->chat->apiRouter();
    $this->request = new StubHttpRequest('POST', '/api/user/register', [], [
        'name' => 'Ivan',
    ]);
    $this->registeredUser = createUser(id: 1, name: 'Ivan');
});

it('should return 201 Created', function (): void {
    $response = sendRequestToApi($this->router, $this->request, createUser(role: Role::ADMIN));
    expect($response)->statusCode()->toBe(HttpStatusCode::CREATED);
});

it('should return 403 Forbidden for not an admin', function (): void {
    $response = sendRequestToApi($this->router, $this->request, createUser());
    expect($response)->statusCode()->toBe(HttpStatusCode::FORBIDDEN);
});

it('should return an access token', function (): void {
    $response = sendRequestToApi($this->router, $this->request, createUser(role: Role::ADMIN));
    $data = $response->data();
    expect($data)->toHaveKey('accessToken')
        ->and($data['accessToken'])->not->toBeEmpty();
});

it('should return an user', function (): void {
    $response = sendRequestToApi($this->router, $this->request, createUser(role: Role::ADMIN));
    $data = $response->data();
    expect($data)->toHaveKey('user')
        ->and($data['user'])->toBe($this->registeredUser->toChatPayload());
});

it('should add the user to the storage', function (): void {
    $response = sendRequestToApi($this->router, $this->request, createUser(role: Role::ADMIN));
    $data = $response->data();
    $accessToken = $data['accessToken'] ?? '';
    $user = createUser(id: 1, name: 'Ivan', accessToken: $accessToken);
    expect($this->userStorage->findByToken($accessToken))->toEqual($user);
});

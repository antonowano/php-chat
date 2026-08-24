<?php

use Antonowano\Chat\Chat;
use Antonowano\Chat\Enums\HttpStatusCode;
use Antonowano\Chat\Role;
use Antonowano\Chat\Stubs\StubHttpRequest;
use Symfony\Component\Clock\MockClock;

beforeEach(function (): void {
    $this->clock = new MockClock();
    $this->chat = new Chat($this->clock);
    $this->messageStorage = $this->chat->messageStorage();
    $this->userStorage = $this->chat->userStorage();
    $this->roomStorage = $this->chat->roomStorage();
    $this->router = $this->chat->apiRouter();
});

describe('User registration', function (): void {
    beforeEach(function (): void {
        $this->request = new StubHttpRequest('POST', '/api/user/register', [], [
            'name' => 'Ivan',
        ]);
    });

    describe('For admin', function (): void {
        beforeEach(function (): void {
            $this->response = sendRequestToApi($this->router, $this->request, createUser(role: Role::ADMIN));
            $this->accessToken = $this->response->data()['accessToken'] ?? '';
        });

        it('should return 201 Created', function (): void {
            expect($this->response->statusCode())->toBe(HttpStatusCode::CREATED);
        });

        it('should return access token', function (): void {
            expect($this->accessToken)->not()->toBeEmpty();
        });

        it('should add the user to the storage', function (): void {
            $expectedUser = createUser(id: 1, name: 'Ivan', accessToken: $this->accessToken);
            expect($this->userStorage->findByToken($this->accessToken))->toEqual($expectedUser);
        });
    });

    describe('For a regular user', function (): void {
        beforeEach(function (): void {
            $this->response = sendRequestToApi($this->router, $this->request);
        });

        it('should return 403 Forbidden', function (): void {
            expect($this->response)
                ->statusCode()->toBe(HttpStatusCode::FORBIDDEN)
                ->data()->toHaveKey('error');
        });

        it('should not add users to the storage', function (): void {
            expect($this->userStorage->findAllById([1]))->toBeEmpty();
        });
    });
});

describe('Room registration', function (): void {
    beforeEach(function (): void {
        $this->user1 = $this->userStorage->create(createNewUser());
        $this->user2 = $this->userStorage->create(createNewUser());
        $this->request = new StubHttpRequest('POST', '/api/room/register', [], [
            'memberIds' => [$this->user1->id(), $this->user2->id()],
        ]);
    });

    describe('For admin', function (): void {
        beforeEach(function (): void {
            $this->response = sendRequestToApi($this->router, $this->request, createUser(role: Role::ADMIN));
        });

        it('should return 201 Created', function (): void {
            expect($this->response)
                ->statusCode()->toBe(HttpStatusCode::CREATED);
        });

        it('should add the room to the storage', function (): void {
            $expectedRoom = createRoom(
                id: 1,
                members: [$this->user1, $this->user2],
            );
            expect($this->roomStorage->findById(1))->toEqual($expectedRoom);
        });
    });

    describe('For a regular user', function (): void {
        beforeEach(function (): void {
            $this->response = sendRequestToApi($this->router, $this->request);
        });

        it('should return 403 Forbidden', function (): void {
            expect($this->response)
                ->statusCode()->toBe(HttpStatusCode::FORBIDDEN)
                ->data()->toHaveKey('error');
        });

        it('should not add rooms to the storage', function (): void {
            expect($this->roomStorage->findById(1))->toBeNull();
        });
    });
});

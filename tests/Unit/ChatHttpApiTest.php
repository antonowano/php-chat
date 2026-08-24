<?php

use Antonowano\Chat\AccessControl;
use Antonowano\Chat\Api\ApiController;
use Antonowano\Chat\Api\ApiRouter;
use Antonowano\Chat\Enums\HttpStatusCode;
use Antonowano\Chat\Events;
use Antonowano\Chat\Message;
use Antonowano\Chat\MessageStorage;
use Antonowano\Chat\Role;
use Antonowano\Chat\RoomStorage;
use Antonowano\Chat\Stubs\StubHttpRequest;
use Antonowano\Chat\UserStorage;
use Symfony\Component\Clock\MockClock;
use Tests\Antonowano\Chat\Unit\TestCase;

uses(TestCase::class);

beforeEach(function (): void {
    $this->clock = new MockClock();
    $this->messageStorage = new MessageStorage($this->clock);
    $this->userStorage = new UserStorage();
    $this->roomStorage = new RoomStorage($this->userStorage);
    $this->router = new ApiRouter(new ApiController(
        new Events(),
        $this->userStorage,
        $this->messageStorage,
        $this->roomStorage,
        new AccessControl()
    ));
});

describe('User registration by admin', function (): void {
    beforeEach(function (): void {
        $request = new StubHttpRequest('POST', '/api/user/register', [], [
            'name' => 'Ivan',
        ]);
        $this->response = sendRequestToApi($this->router, $request, createUser(role: Role::ADMIN));
        $this->accessToken = $this->response->data()['accessToken'] ?? '';
    });

    it('should return 201 Created', function (): void {
        expect($this->response->statusCode())->toBe(HttpStatusCode::CREATED);
    });

    it('should return access token', function (): void {
        expect($this->accessToken)->not()->toBeEmpty();
    });

    it('should be accessible from the user storage', function (): void {
        $expectedUser = createUser(id: 1, name: 'Ivan', accessToken: $this->accessToken);
        expect($this->userStorage->findByToken($this->accessToken))->toEqual($expectedUser);
    });
});

describe('User registration by another user', function (): void {
    beforeEach(function (): void {
        $request = new StubHttpRequest('POST', '/api/user/register', [], [
            'name' => 'Olga',
        ]);
        $this->response = sendRequestToApi($this->router, $request);
    });

    it('should return 403 Forbidden', function (): void {
        expect($this->response)
            ->statusCode()->toBe(HttpStatusCode::FORBIDDEN)
            ->data()->toHaveKey('error');
    });
});

describe('Room registration', function (): void {
    beforeEach(function (): void {
        $this->user1 = $this->userStorage->create(createNewUser());
        $this->user2 = $this->userStorage->create(createNewUser());
        $request = new StubHttpRequest('POST', '/api/room/register', [], [
            'memberIds' => [$this->user1->id(), $this->user2->id()],
        ]);
        $this->response = sendRequestToApi($this->router, $request, createUser(role: Role::ADMIN));
    });

    it('should return 201 Created', function (): void {
        expect($this->response->statusCode())->toBe(HttpStatusCode::CREATED);
    });

    it('should be accessible from the user storage', function (): void {
        $expectedRoom = createRoom(
            id: 1,
            members: [$this->user1, $this->user2],
        );
        expect($this->roomStorage->findById(1))->toEqual($expectedRoom);
    });
});

describe('Sending a message', function (): void {
    beforeEach(function (): void {
        $request = new StubHttpRequest('POST', '/api/message/send', [], [
            'roomId' => 1,
            'text' => 'Hello World!',
        ]);
        $this->response = sendRequestToApi($this->router, $request, createUser(name: 'John Doe'));
        $this->messageInChat1 = $this->messageStorage->getLastMessages(1, 10);
        $this->messageInChat2 = $this->messageStorage->getLastMessages(2, 10);
    });

    it('should return 201 Created', function (): void {
        expect($this->response->statusCode())->toBe(HttpStatusCode::CREATED);
    });

    it('should matches the sent message', function (): void {
        /** @var Message $message */
        $message = $this->messageInChat1[0];
        expect($message->id())->toBe(1)
            ->and($message->text())->toBe('Hello World!')
            ->and($message->roomId())->toBe(1)
            ->and($message->author()->name())->toBe('John Doe');
    });

    it('should store exactly one message in the chat', function (): void {
        expect($this->messageInChat1)->toHaveCount(1);
    });

    it('should not store message in another chat', function (): void {
        expect($this->messageInChat2)->toHaveCount(0);
    });
});

describe('Fetching latest messages', function (): void {
    $roomId = 1;
    $limit = 30;

    beforeEach(function () use ($roomId): void {
        $this->messages = $this->fillChat($this->messageStorage, $this->clock);
        $request = new StubHttpRequest('GET', '/api/messages/last', [
            'roomId' => $roomId,
        ]);
        $this->response = sendRequestToApi($this->router, $request);
    });

    it('should return 200 OK', function (): void {
        expect($this->response->statusCode())->toBe(HttpStatusCode::OK);
    });

    it("should return the last {$limit} messages", function () use ($limit, $roomId): void {
        $expectedMessages = array_filter($this->messages, fn (Message $m): bool => $m->roomId() === $roomId);
        $expectedMessages = array_slice($expectedMessages, -$limit);
        expect($this->response->data())->toBe(['messages' => payloadOfMessages($expectedMessages)]);
    });
});

describe('Fetching next messages', function (): void {
    $roomId = 1;
    $afterId = 3;
    $limit = 30;

    beforeEach(function () use ($roomId, $afterId): void {
        $this->messages = $this->fillChat($this->messageStorage, $this->clock);
        $request = new StubHttpRequest('GET', '/api/messages/next', [
            'roomId' => $roomId,
            'id' => $afterId,
        ]);
        $this->response = sendRequestToApi($this->router, $request);
    });

    it('should return 200 OK', function (): void {
        expect($this->response->statusCode())->toBe(HttpStatusCode::OK);
    });

    it(
        "should return {$limit} messages with an ID greater than {$afterId}",
        function () use ($limit, $roomId, $afterId): void {
            $expectedMessages = array_filter(
                $this->messages,
                fn (Message $m): bool => $m->roomId() === $roomId && $m->id() > $afterId
            );
            $expectedMessages = array_slice($expectedMessages, 0, $limit);
            expect($this->response->data())->toBe(['messages' => payloadOfMessages($expectedMessages)]);
        }
    );
});

describe('Fetching previous messages', function (): void {
    $roomId = 1;
    $beforeId = 3;
    $limit = 30;

    beforeEach(function () use ($roomId, $beforeId): void {
        $this->messages = $this->fillChat($this->messageStorage, $this->clock);
        $request = new StubHttpRequest('GET', '/api/messages/previous', [
            'roomId' => $roomId,
            'id' => $beforeId,
        ]);
        $this->response = sendRequestToApi($this->router, $request);
    });

    it('should return 200 OK', function (): void {
        expect($this->response->statusCode())->toBe(HttpStatusCode::OK);
    });

    it(
        "should return {$limit} messages with an ID less than {$beforeId}",
        function () use ($limit, $roomId, $beforeId): void {
            $expectedMessages = array_filter(
                $this->messages,
                fn (Message $m): bool => $m->roomId() === $roomId && $m->id() < $beforeId
            );
            $expectedMessages = array_slice($expectedMessages, -$limit);
            expect($this->response->data())->toBe(['messages' => payloadOfMessages($expectedMessages)]);
        }
    );
});

describe('Accessing non-existent route', function (): void {
    beforeEach(function (): void {
        $request = new StubHttpRequest('POST', '/not-found');
        $this->response = sendRequestToApi($this->router, $request);
    });

    it('should return 404 Not Found', function (): void {
        expect($this->response->statusCode())->toBe(HttpStatusCode::NOT_FOUND);
    });

    it('should contains a error message', function (): void {
        expect($this->response->data())->toHaveKey('error');
    });
});

<?php

use Antonowano\Chat\Chat;
use Antonowano\Chat\Enums\HttpStatusCode;
use Antonowano\Chat\Message;
use Antonowano\Chat\NewRoom;
use Antonowano\Chat\Room;
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

describe('Sending a message', function (): void {
    beforeEach(function (): void {
        $user = $this->userStorage->create(createNewUser(name: 'John Doe'));
        $room = $this->roomStorage->create(new NewRoom(
            memberIds: [$user->id()],
        ));
        $request = new StubHttpRequest('POST', '/api/message/send', [], [
            'roomId' => $room->id(),
            'text' => 'Hello World!',
        ]);
        $this->response = sendRequestToApi($this->router, $request, $user);
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

describe('Fetching the room list', function (): void {
    $limit = 3;
    $offset = 1;

    beforeEach(function () use ($offset, $limit): void {
        $this->user = $this->userStorage->create(createNewUser());
        $this->rooms = [
            $this->roomStorage->create(createNewRoom()),
            $this->roomStorage->create(createNewRoom([$this->user->id()])),
            $this->roomStorage->create(createNewRoom([$this->user->id()])),
            $this->roomStorage->create(createNewRoom([$this->user->id()])),
            $this->roomStorage->create(createNewRoom([$this->user->id()])),
            $this->roomStorage->create(createNewRoom([$this->user->id()])),
            $this->roomStorage->create(createNewRoom()),
        ];
        $request = new StubHttpRequest('GET', '/api/rooms', [
            'offset' => $offset,
            'limit' => $limit,
        ]);
        $this->response = sendRequestToApi($this->router, $request, $this->user);
    });

    it('should return 200 OK', function (): void {
        expect($this->response->statusCode())->toBe(HttpStatusCode::OK);
    });

    it('should return a list of the user\'s rooms', function () use ($offset, $limit): void {
        $expectedRooms = array_filter($this->rooms, fn (Room $m): bool => $m->hasMember($this->user));
        $expectedRooms = array_slice($expectedRooms, $offset, $limit);
        expect($this->response->data())->toBe(['rooms' => payloadOfRooms($expectedRooms)]);
    });
});

describe('Fetching latest messages', function (): void {
    $roomId = 1;
    $limit = 30;

    beforeEach(function () use ($roomId): void {
        $this->messages = createFullChat($this->userStorage, $this->roomStorage, $this->messageStorage);
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
        $this->messages = createFullChat($this->userStorage, $this->roomStorage, $this->messageStorage);
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
        $this->messages = createFullChat($this->userStorage, $this->roomStorage, $this->messageStorage);
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

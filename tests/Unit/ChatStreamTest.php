<?php

use Antonowano\Chat\Chat;
use Antonowano\Chat\Message;
use Antonowano\Chat\Room;
use Antonowano\Chat\Stubs\StubWsFrame;
use Symfony\Component\Clock\MockClock;

beforeEach(function (): void {
    $this->clock = new MockClock();
    $this->chat = new Chat($this->clock);
    $this->messageStorage = $this->chat->messageStorage();
    $this->userStorage = $this->chat->userStorage();
    $this->roomStorage = $this->chat->roomStorage();
    $this->router = $this->chat->streamRouter();
});

describe('Sending a message', function (): void {
    beforeEach(function (): void {
        $this->user = $this->userStorage->create(createNewUser(name: 'John Doe'));
        $this->room = $this->roomStorage->create(createNewRoom(
            memberIds: [$this->user->id()],
        ));
        $this->frame = new StubWsFrame([
            'type' => 'NewMessage',
            'data' => [
                'roomId' => $this->room->id(),
                'text' => 'Hello World!',
            ],
        ]);
    });

    it('should not send messages when the user is not a member', function (): void {
        $otherUser = $this->userStorage->create(createNewUser(name: 'Ivan'));
        sendRequestToWs($this->router, $this->frame, $otherUser);
        $messages = $this->messageStorage->getLastMessages($this->room->id(), 10);
        expect($messages)->toHaveCount(0);
    });

    it('should matches the sent message', function (): void {
        sendRequestToWs($this->router, $this->frame, $this->user);
        $messages = $this->messageStorage->getLastMessages($this->room->id(), 10);
        $message = $messages[0];
        expect($message)->not->toBeNull()
            ->id()->toBe(1)
            ->text()->toBe('Hello World!')
            ->roomId()->toBe(1)
            ->and($message->author()->name())->toBe('John Doe');
    });

    it('should store exactly one message in the chat', function (): void {
        sendRequestToWs($this->router, $this->frame, $this->user);
        $messages = $this->messageStorage->getLastMessages($this->room->id(), 10);
        expect($messages)->toHaveCount(1);
    });

    it('should not store message in another chat', function (): void {
        sendRequestToWs($this->router, $this->frame, $this->user);
        $messages = $this->messageStorage->getLastMessages($this->room->id() + 1, 10);
        expect($messages)->toHaveCount(0);
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
        $frame = new StubWsFrame([
            'type' => 'RoomList',
            'data' => [
                'offset' => $offset,
                'limit' => $limit,
            ],
        ]);
        $this->response = sendRequestToWs($this->router, $frame, $this->user);
    });

    it('should return a list of the user\'s rooms', function () use ($offset, $limit): void {
        $expectedRooms = array_filter($this->rooms, fn (Room $m): bool => $m->hasMember($this->user));
        $expectedRooms = array_slice($expectedRooms, $offset, $limit);
        expect($this->response->data())->toBe([
            'type' => 'RoomList',
            'data' => payloadOfRooms($expectedRooms),
        ]);
    });
});

describe('Fetching latest messages', function (): void {
    $roomId = 1;
    $limit = 30;

    beforeEach(function () use ($roomId): void {
        $this->messages = createFullChat($this->userStorage, $this->roomStorage, $this->messageStorage);
        $frame = new StubWsFrame([
            'type' => 'LastMessages',
            'data' => [
                'roomId' => 1,
            ],
        ]);
        $this->response = sendRequestToWs($this->router, $frame);
    });

    it("should return the last {$limit} messages", function () use ($limit, $roomId): void {
        $expectedMessages = array_filter($this->messages, fn (Message $m): bool => $m->roomId() === $roomId);
        $expectedMessages = array_slice($expectedMessages, -$limit);
        expect($this->response->data())->toBe([
            'type' => 'LastMessages',
            'data' => payloadOfMessages($expectedMessages),
        ]);
    });
});

describe('Fetching next messages', function (): void {
    $roomId = 1;
    $afterId = 3;
    $limit = 30;

    beforeEach(function () use ($roomId, $afterId): void {
        $this->messages = createFullChat($this->userStorage, $this->roomStorage, $this->messageStorage);
        $frame = new StubWsFrame([
            'type' => 'NextMessages',
            'data' => [
                'roomId' => $roomId,
                'id' => $afterId,
            ]
        ]);
        $this->response = sendRequestToWs($this->router, $frame);
    });

    it(
        "should return {$limit} messages with an ID greater than {$afterId}",
        function () use ($limit, $roomId, $afterId): void {
            $expectedMessages = array_filter(
                $this->messages,
                fn (Message $m): bool => $m->roomId() === $roomId && $m->id() > $afterId
            );
            $expectedMessages = array_slice($expectedMessages, 0, $limit);
            expect($this->response->data())->toBe([
                'type' => 'NextMessages',
                'data' => payloadOfMessages($expectedMessages),
            ]);
        }
    );
});

describe('Fetching previous messages', function (): void {
    $roomId = 1;
    $beforeId = 3;
    $limit = 30;

    beforeEach(function () use ($roomId, $beforeId): void {
        $this->messages = createFullChat($this->userStorage, $this->roomStorage, $this->messageStorage);
        $frame = new StubWsFrame([
            'type' => 'PreviousMessages',
            'data' => [
                'roomId' => $roomId,
                'id' => $beforeId,
            ]
        ]);
        $this->response = sendRequestToWs($this->router, $frame);
    });

    it(
        "should return {$limit} messages with an ID less than {$beforeId}",
        function () use ($limit, $roomId, $beforeId): void {
            $expectedMessages = array_filter(
                $this->messages,
                fn (Message $m): bool => $m->roomId() === $roomId && $m->id() < $beforeId
            );
            $expectedMessages = array_slice($expectedMessages, -$limit);
            expect($this->response->data())->toBe([
                'type' => 'PreviousMessages',
                'data' => payloadOfMessages($expectedMessages),
            ]);
        }
    );
});

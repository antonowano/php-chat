<?php

use Antonowano\Chat\Chat;
use Antonowano\Chat\Message;
use Antonowano\Chat\Stubs\StubWsFrame;
use Symfony\Component\Clock\MockClock;
use Tests\Antonowano\Chat\Unit\TestCase;

uses(TestCase::class);

beforeEach(function (): void {
    $this->clock = new MockClock();
    $this->chat = new Chat($this->clock);
    $this->messageStorage = $this->chat->messageStorage();
    $this->router = $this->chat->streamRouter();
});

describe('Sending a message', function (): void {
    $user = createUser(name: 'John Doe');

    beforeEach(function () use ($user): void {
        $frame = new StubWsFrame([
            'type' => 'NewMessage',
            'data' => [
                'roomId' => 1,
                'text' => 'Hello World!',
            ],
        ]);
        $this->response = sendRequestToWs($this->router, $frame, $user);
        $this->messageInChat1 = $this->messageStorage->getLastMessages(1, 10);
        $this->messageInChat2 = $this->messageStorage->getLastMessages(2, 10);
    });

    it('should matches the sent message', function () use ($user): void {
        $expected = createMessage(1, 'Hello World!', $this->clock->now(), 1, $user);
        expect($expected)->toEqual($this->messageInChat1[0]);
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
        $this->messages = $this->fillChat($this->messageStorage, $this->clock);
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
        $this->messages = $this->fillChat($this->messageStorage, $this->clock);
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

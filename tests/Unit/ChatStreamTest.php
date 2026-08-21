<?php

use Antonowano\Chat\Chat;
use Antonowano\Chat\Message;
use Antonowano\Chat\Stream\StreamController;
use Antonowano\Chat\Stream\StreamFrame;
use Antonowano\Chat\Stream\StreamResponse;
use Antonowano\Chat\Stream\StreamRouter;
use Antonowano\Chat\Stubs\StubWsFrame;
use Antonowano\Chat\Stubs\StubWsResponse;
use Symfony\Component\Clock\MockClock;
use Tests\Antonowano\Chat\Unit\TestCase;

uses(TestCase::class);

beforeEach(function (): void {
    $this->clock = new MockClock();
    $this->chat = new Chat($this->clock);
    $this->router = new StreamRouter(new StreamController($this->chat));
});

describe('Sending a message', function (): void {
    beforeEach(function (): void {
        $frame = new StubWsFrame([
            'type' => 'NewMessage',
            'data' => [
                'chatId' => 1,
                'text' => 'Hello World!',
                'author' => 'John Doe',
            ],
        ]);
        $this->response = new StubWsResponse();
        $this->router->dispatch(new StreamFrame($frame), new StreamResponse($this->response));
        $this->messageInChat1 = $this->chat->getLastMessages(1, 10);
        $this->messageInChat2 = $this->chat->getLastMessages(2, 10);
    });

    it('should matches the sent message', function (): void {
        $expected = createMessage(1, 'Hello World!', $this->clock->now(), 'John Doe');
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
    $chatId = 1;
    $limit = 30;

    beforeEach(function () use ($chatId): void {
        $this->messages = $this->fillChat($this->chat, $this->clock);
        $frame = new StubWsFrame([
            'type' => 'LastMessages',
            'data' => [
                'chatId' => 1,
            ],
        ]);
        $this->response = new StubWsResponse();
        $this->router->dispatch(new StreamFrame($frame), new StreamResponse($this->response));
    });

    it("should return the last {$limit} messages", function () use ($limit, $chatId): void {
        $expectedMessages = array_filter($this->messages, fn (Message $m): bool => $m->chatId() === $chatId);
        $expectedMessages = array_slice($expectedMessages, -$limit);
        expect($this->response->data())->toBe([
            'type' => 'LastMessages',
            'data' => payloadOfMessages($expectedMessages),
        ]);
    });
});

describe('Fetching next messages', function (): void {
    $chatId = 1;
    $afterId = 3;
    $limit = 30;

    beforeEach(function () use ($chatId, $afterId): void {
        $this->messages = $this->fillChat($this->chat, $this->clock);
        $frame = new StubWsFrame([
            'type' => 'NextMessages',
            'data' => [
                'chatId' => $chatId,
                'id' => $afterId,
            ]
        ]);
        $this->response = new StubWsResponse();
        $this->router->dispatch(new StreamFrame($frame), new StreamResponse($this->response));
    });

    it(
        "should return {$limit} messages with an ID greater than {$afterId}",
        function () use ($limit, $chatId, $afterId): void {
            $expectedMessages = array_filter(
                $this->messages,
                fn (Message $m): bool => $m->chatId() === $chatId && $m->id() > $afterId
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
    $chatId = 1;
    $beforeId = 3;
    $limit = 30;

    beforeEach(function () use ($chatId, $beforeId): void {
        $this->messages = $this->fillChat($this->chat, $this->clock);
        $frame = new StubWsFrame([
            'type' => 'PreviousMessages',
            'data' => [
                'chatId' => $chatId,
                'id' => $beforeId,
            ]
        ]);
        $this->response = new StubWsResponse();
        $this->router->dispatch(new StreamFrame($frame), new StreamResponse($this->response));
    });

    it(
        "should return {$limit} messages with an ID less than {$beforeId}",
        function () use ($limit, $chatId, $beforeId): void {
            $expectedMessages = array_filter(
                $this->messages,
                fn (Message $m): bool => $m->chatId() === $chatId && $m->id() < $beforeId
            );
            $expectedMessages = array_slice($expectedMessages, -$limit);
            expect($this->response->data())->toBe([
                'type' => 'PreviousMessages',
                'data' => payloadOfMessages($expectedMessages),
            ]);
        }
    );
});

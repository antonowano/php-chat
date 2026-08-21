<?php

use Antonowano\Chat\Chat;
use Antonowano\Chat\Stream\StreamController;
use Antonowano\Chat\Stream\StreamFrame;
use Antonowano\Chat\Stream\StreamResponse;
use Antonowano\Chat\Stream\StreamRouter;
use Antonowano\Chat\Stubs\StubWsFrame;
use Antonowano\Chat\Stubs\StubWsResponse;
use Antonowano\Chat\Swoole\SwooleWsChatListener;
use Symfony\Component\Clock\MockClock;
use Tests\Antonowano\Chat\Unit\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->clock = new MockClock();
    $this->chat = new Chat($this->clock);
    $this->router = new StreamRouter(new StreamController($this->chat));
});

test('send message', function () {
    $listenerResponse = new StubWsResponse();
    $this->chat->addListener(
        SwooleWsChatListener::generateId(1),
        new SwooleWsChatListener($listenerResponse)
    );

    $frame = new StubWsFrame([
        'type' => 'NewMessage',
        'data' => [
            'chatId' => 1,
            'text' => 'Hello World!',
            'author' => 'John Doe',
        ],
    ]);
    $response = new StubWsResponse();
    $this->router->dispatch(new StreamFrame($frame), new StreamResponse($response));
    $message = $this->createMessage(1, 'Hello World!', $this->clock->now(), 'John Doe');

    $this->assertObjectListEquals(
        [$message],
        $this->chat->getLastMessages(1, 10)
    );

    expect($listenerResponse->data())->toBe([
        'type' => 'Message',
        'data' => $message->toChatPayload(),
    ]);
});

test('last messages', function () {
    $expectedMessages = array_slice($this->fillChat($this->chat, $this->clock), 1, 5);
    $frame = new StubWsFrame([
        'type' => 'LastMessages',
        'data' => [
            'chatId' => 1,
        ],
    ]);
    $response = new StubWsResponse();
    $this->router->dispatch(new StreamFrame($frame), new StreamResponse($response));

    expect($response->data())->toBe([
        'type' => 'LastMessages',
        'data' => array_map(fn($message) => $message->toChatPayload(), $expectedMessages),
    ]);
});

test('next messages', function () {
    $expectedMessages = array_slice($this->fillChat($this->chat, $this->clock), 3, 3);
    $frame = new StubWsFrame([
        'type' => 'NextMessages',
        'data' => [
            'chatId' => 1,
            'id' => 3,
        ]
    ]);
    $response = new StubWsResponse();
    $this->router->dispatch(new StreamFrame($frame), new StreamResponse($response));

    expect($response->data())->toBe([
        'type' => 'NextMessages',
        'data' => array_map(fn($message) => $message->toChatPayload(), $expectedMessages),
    ]);
});

test('previous messages', function () {
    $expectedMessages = array_slice($this->fillChat($this->chat, $this->clock), 1, 1);
    $frame = new StubWsFrame([
        'type' => 'PreviousMessages',
        'data' => [
            'chatId' => 1,
            'id' => 3,
        ]
    ]);
    $response = new StubWsResponse();
    $this->router->dispatch(new StreamFrame($frame), new StreamResponse($response));

    expect($response->data())->toBe([
        'type' => 'PreviousMessages',
        'data' => array_map(fn($message) => $message->toChatPayload(), $expectedMessages),
    ]);
});

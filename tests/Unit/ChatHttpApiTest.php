<?php

use Antonowano\Chat\Api\ApiController;
use Antonowano\Chat\Api\ApiRequest;
use Antonowano\Chat\Api\ApiResponse;
use Antonowano\Chat\Api\ApiRouter;
use Antonowano\Chat\Chat;
use Antonowano\Chat\Enums\HttpStatusCode;
use Antonowano\Chat\Stubs\StubHttpRequest;
use Antonowano\Chat\Stubs\StubHttpResponse;
use Symfony\Component\Clock\MockClock;
use Tests\Antonowano\Chat\Unit\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->clock = new MockClock();
    $this->chat = new Chat($this->clock);
    $this->router = new ApiRouter(new ApiController($this->chat));
});

test('route not found', function () {
    $request = new StubHttpRequest('POST', '/not-found');
    $response = new StubHttpResponse();
    $this->router->dispatch(new ApiRequest($request), new ApiResponse($response));
    expect($response->statusCode())->toBe(HttpStatusCode::NOT_FOUND)
        ->and($response->data())->toHaveKey('error');
});

test('send message', function () {
    $request = new StubHttpRequest('POST', '/api/message/send', [], [
        'chatId' => 1,
        'author' => 'John Doe',
        'text' => 'Hello World!',
    ]);
    $response = new StubHttpResponse();
    $this->router->dispatch(new ApiRequest($request), new ApiResponse($response));
    $request = new StubHttpRequest('POST', '/api/message/send', [], [
        'chatId' => 2,
        'author' => 'Alex',
        'text' => 'See you later',
    ]);
    $this->router->dispatch(new ApiRequest($request), new ApiResponse($response));

    expect($response->statusCode())->toBe(HttpStatusCode::CREATED)
        ->and($response->data())->toBe([]);
    $this->assertObjectListEquals(
        [
            $this->createMessage(1, 'Hello World!', $this->clock->now(), 'John Doe'),
        ],
        $this->chat->getLastMessages(1, 10)
    );
    $this->assertObjectListEquals(
        [
            $this->createMessage(2, 'See you later', $this->clock->now(), 'Alex'),
        ],
        $this->chat->getLastMessages(2, 10)
    );
    $this->assertObjectListEquals(
        [],
        $this->chat->getLastMessages(3, 10)
    );
});

test('last messages', function () {
    $expectedMessages = array_slice($this->fillChat($this->chat, $this->clock), 1, 5);
    $request = new StubHttpRequest('GET', '/api/messages/last', [
        'chatId' => 1,
    ]);
    $response = new StubHttpResponse();
    $this->router->dispatch(new ApiRequest($request), new ApiResponse($response));

    expect($response->statusCode())->toBe(HttpStatusCode::OK)
        ->and($response->data())
        ->toBe(['messages' => array_map(fn($m) => $m->toChatPayload(), $expectedMessages)]);
});

test('next messages', function () {
    $expectedMessages = array_slice($this->fillChat($this->chat, $this->clock), 3, 3);
    $request = new StubHttpRequest('GET', '/api/messages/next', [
        'chatId' => 1,
        'id' => 3,
    ]);
    $response = new StubHttpResponse();
    $this->router->dispatch(new ApiRequest($request), new ApiResponse($response));

    expect($response->statusCode())->toBe(HttpStatusCode::OK)
        ->and($response->data())
        ->toBe(['messages' => array_map(fn($m) => $m->toChatPayload(), $expectedMessages)]);
});

test('previous messages', function () {
    $expectedMessages = array_slice($this->fillChat($this->chat, $this->clock), 1, 1);
    $request = new StubHttpRequest('GET', '/api/messages/previous', [
        'chatId' => 1,
        'id' => 3,
    ]);
    $response = new StubHttpResponse();
    $this->router->dispatch(new ApiRequest($request), new ApiResponse($response));

    expect($response->statusCode())->toBe(HttpStatusCode::OK)
        ->and($response->data())
        ->toBe(['messages' => array_map(fn($m) => $m->toChatPayload(), $expectedMessages)]);
});

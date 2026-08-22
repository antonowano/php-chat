<?php

use Antonowano\Chat\Api\ApiController;
use Antonowano\Chat\Api\ApiRequest;
use Antonowano\Chat\Api\ApiResponse;
use Antonowano\Chat\Api\ApiRouter;
use Antonowano\Chat\Chat;
use Antonowano\Chat\Enums\HttpStatusCode;
use Antonowano\Chat\Message;
use Antonowano\Chat\Stubs\StubHttpRequest;
use Antonowano\Chat\Stubs\StubHttpResponse;
use Antonowano\Chat\UserStorage;
use Symfony\Component\Clock\MockClock;
use Tests\Antonowano\Chat\Unit\TestCase;

uses(TestCase::class);

beforeEach(function (): void {
    $this->clock = new MockClock();
    $this->chat = new Chat($this->clock);
    $this->userStorage = new UserStorage();
    $this->router = new ApiRouter(new ApiController($this->chat, $this->userStorage));
});

describe('User registration', function (): void {
    beforeEach(function (): void {
        $request = new StubHttpRequest('POST', '/api/user/register', [], [
            'name' => 'Ivan',
        ]);
        $this->response = new StubHttpResponse();
        $this->router->dispatch(new ApiRequest($request), new ApiResponse($this->response));
        $this->accessToken = $this->response->data()['accessToken'] ?? '';
    });

    it('should return 201 Created', function (): void {
        expect($this->response->statusCode())->toBe(HttpStatusCode::CREATED);
    });

    it('should return access token', function (): void {
        expect($this->accessToken)->not()->toBeEmpty();
    });

    it('should be accessible from the user storage', function (): void {
        expect($this->userStorage->findNameByToken($this->accessToken))->toBe('Ivan');
    });
});

describe('Sending a message', function (): void {
    beforeEach(function (): void {
        $accessToken = $this->userStorage->register('John Doe');
        $request = new StubHttpRequest('POST', '/api/message/send', [], [
            'chatId' => 1,
            'text' => 'Hello World!',
        ], [
            'Authorization' => 'Bearer ' . $accessToken,
        ]);
        $this->response = new StubHttpResponse();
        $this->router->dispatch(new ApiRequest($request), new ApiResponse($this->response));
        $this->messageInChat1 = $this->chat->getLastMessages(1, 10);
        $this->messageInChat2 = $this->chat->getLastMessages(2, 10);
    });

    it('should return 201 Created', function (): void {
        expect($this->response->statusCode())->toBe(HttpStatusCode::CREATED);
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
        $request = new StubHttpRequest('GET', '/api/messages/last', [
            'chatId' => $chatId,
        ]);
        $this->response = new StubHttpResponse();
        $this->router->dispatch(new ApiRequest($request), new ApiResponse($this->response));
    });

    it('should return 200 OK', function (): void {
        expect($this->response->statusCode())->toBe(HttpStatusCode::OK);
    });

    it("should return the last {$limit} messages", function () use ($limit, $chatId): void {
        $expectedMessages = array_filter($this->messages, fn (Message $m): bool => $m->chatId() === $chatId);
        $expectedMessages = array_slice($expectedMessages, -$limit);
        expect($this->response->data())->toBe(['messages' => payloadOfMessages($expectedMessages)]);
    });
});

describe('Fetching next messages', function (): void {
    $chatId = 1;
    $afterId = 3;
    $limit = 30;

    beforeEach(function () use ($chatId, $afterId): void {
        $this->messages = $this->fillChat($this->chat, $this->clock);
        $request = new StubHttpRequest('GET', '/api/messages/next', [
            'chatId' => $chatId,
            'id' => $afterId,
        ]);
        $this->response = new StubHttpResponse();
        $this->router->dispatch(new ApiRequest($request), new ApiResponse($this->response));
    });

    it('should return 200 OK', function (): void {
        expect($this->response->statusCode())->toBe(HttpStatusCode::OK);
    });

    it(
        "should return {$limit} messages with an ID greater than {$afterId}",
        function () use ($limit, $chatId, $afterId): void {
            $expectedMessages = array_filter(
                $this->messages,
                fn (Message $m): bool => $m->chatId() === $chatId && $m->id() > $afterId
            );
            $expectedMessages = array_slice($expectedMessages, 0, $limit);
            expect($this->response->data())->toBe(['messages' => payloadOfMessages($expectedMessages)]);
        }
    );
});

describe('Fetching previous messages', function (): void {
    $chatId = 1;
    $beforeId = 3;
    $limit = 30;

    beforeEach(function () use ($chatId, $beforeId): void {
        $this->messages = $this->fillChat($this->chat, $this->clock);
        $request = new StubHttpRequest('GET', '/api/messages/previous', [
            'chatId' => $chatId,
            'id' => $beforeId,
        ]);
        $this->response = new StubHttpResponse();
        $this->router->dispatch(new ApiRequest($request), new ApiResponse($this->response));
    });

    it('should return 200 OK', function (): void {
        expect($this->response->statusCode())->toBe(HttpStatusCode::OK);
    });

    it(
        "should return {$limit} messages with an ID less than {$beforeId}",
        function () use ($limit, $chatId, $beforeId): void {
            $expectedMessages = array_filter(
                $this->messages,
                fn (Message $m): bool => $m->chatId() === $chatId && $m->id() < $beforeId
            );
            $expectedMessages = array_slice($expectedMessages, -$limit);
            expect($this->response->data())->toBe(['messages' => payloadOfMessages($expectedMessages)]);
        }
    );
});

describe('Accessing non-existent route', function (): void {
    beforeEach(function (): void {
        $request = new StubHttpRequest('POST', '/not-found');
        $this->response = new StubHttpResponse();
        $this->router->dispatch(new ApiRequest($request), new ApiResponse($this->response));
    });

    it('should return 404 Not Found', function (): void {
        expect($this->response->statusCode())->toBe(HttpStatusCode::NOT_FOUND);
    });

    it('should contains a error message', function (): void {
        expect($this->response->data())->toHaveKey('error');
    });
});

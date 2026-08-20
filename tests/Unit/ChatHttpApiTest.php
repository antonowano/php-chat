<?php

namespace Tests\Antonowano\Chat\Unit;

use Antonowano\Chat\Api\ApiController;
use Antonowano\Chat\Api\ApiRequest;
use Antonowano\Chat\Api\ApiResponse;
use Antonowano\Chat\Api\ApiRouter;
use Antonowano\Chat\Api\HttpResponse;
use Antonowano\Chat\Chat;
use Antonowano\Chat\Enums\HttpStatusCode;
use Antonowano\Chat\Message;
use Antonowano\Chat\Stubs\StubHttpRequest;
use Antonowano\Chat\Stubs\StubHttpResponse;
use Symfony\Component\Clock\MockClock;

class ChatHttpApiTest extends TestCase
{
    private MockClock $clock;
    private Chat $chat;
    private ApiRouter $router;
    private HttpResponse $response;

    protected function setUp(): void
    {
        parent::setUp();
        $this->clock = new MockClock();
        $this->chat = new Chat($this->clock);
        $controller = new ApiController($this->chat);
        $this->router = new ApiRouter($controller);
        $this->response = new StubHttpResponse();
    }

    /**
     * @param list<Message> $expectedMessages
     */
    protected function assertMessageListResponse(array $expectedMessages): void
    {
        $this->assertSame(HttpStatusCode::OK, $this->response->statusCode());
        $this->assertSame(
            ['messages' => array_map(fn ($message) => $message->toChatPayload(), $expectedMessages)],
            $this->response->data()
        );
    }

    public function testRouteNotFound(): void
    {
        $request = new StubHttpRequest('POST', '/not-found');
        $this->router->dispatch(new ApiRequest($request), new ApiResponse($this->response));
        $this->assertSame(HttpStatusCode::NOT_FOUND, $this->response->statusCode());
        $this->assertArrayHasKey('error', $this->response->data());
    }

    public function testSendMessage(): void
    {
        $request = new StubHttpRequest('POST', '/api/message/send', [], [
            'chatId' => 1,
            'author' => 'John Doe',
            'text' => 'Hello World!',
        ]);
        $this->router->dispatch(new ApiRequest($request), new ApiResponse($this->response));
        $request = new StubHttpRequest('POST', '/api/message/send', [], [
            'chatId' => 2,
            'author' => 'Alex',
            'text' => 'See you later',
        ]);
        $this->router->dispatch(new ApiRequest($request), new ApiResponse($this->response));

        $this->assertSame(HttpStatusCode::CREATED, $this->response->statusCode());
        $this->assertSame([], $this->response->data());
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
    }

    public function testLastMessages(): void
    {
        $expectedMessages = array_slice($this->fillChat($this->chat, $this->clock), 1, 5);
        $request = new StubHttpRequest('GET', '/api/messages/last', [
            'chatId' => 1,
        ]);
        $this->router->dispatch(new ApiRequest($request), new ApiResponse($this->response));
        $this->assertMessageListResponse($expectedMessages);
    }

    public function testNextMessages(): void
    {
        $expectedMessages = array_slice($this->fillChat($this->chat, $this->clock), 3, 3);
        $request = new StubHttpRequest('GET', '/api/messages/next', [
            'chatId' => 1,
            'id' => 3,
        ]);
        $this->router->dispatch(new ApiRequest($request), new ApiResponse($this->response));
        $this->assertMessageListResponse($expectedMessages);
    }

    public function testPreviousMessages(): void
    {
        $expectedMessages = array_slice($this->fillChat($this->chat, $this->clock), 1, 1);
        $request = new StubHttpRequest('GET', '/api/messages/previous', [
            'chatId' => 1,
            'id' => 3,
        ]);
        $this->router->dispatch(new ApiRequest($request), new ApiResponse($this->response));
        $this->assertMessageListResponse($expectedMessages);
    }
}

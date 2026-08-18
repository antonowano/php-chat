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
        $this->chat = $this->createChat([], $this->clock);
        $controller = new ApiController($this->chat);
        $this->router = new ApiRouter($controller);
        $this->response = new StubHttpResponse();
    }

    protected function messageTexts(): array
    {
        return [
            ['text' => 'Hi! How was your exam today?', 'author' => 'Ivan'],
            ['text' => 'Hard! I think I failed the last part.', 'author' => 'Olga'],
            ['text' => 'Oh no! Want to grab some coffee?', 'author' => 'Ivan'],
            ['text' => 'Sure! I really need a break now.', 'author' => 'Olga'],
            ['text' => 'Great! See you at 5 pm then.', 'author' => 'Ivan'],
        ];
    }

    /**
     * @return list<Message>
     */
    protected function fillChat(): array
    {
        $messages = [];

        foreach ($this->messageTexts() as $i => $message) {
            $this->chat->sendMessage($this->createNewMessage($message['text'], $message['author']));
            $messages[] = $this->createMessage($i + 1, $message['text'], $this->clock->now(), $message['author']);
        }

        return $messages;
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
            'author' => 'John Doe',
            'text' => 'Hello World!',
        ]);
        $this->router->dispatch(new ApiRequest($request), new ApiResponse($this->response));

        $this->assertSame(HttpStatusCode::CREATED, $this->response->statusCode());
        $this->assertSame([], $this->response->data());
        $this->assertObjectListEquals(
            [
                $this->createMessage(1, 'Hello World!', $this->clock->now(), 'John Doe'),
            ],
            $this->chat->getLastMessages(10)
        );
    }

    public function testLastMessages(): void
    {
        $expectedMessages = $this->fillChat();
        $request = new StubHttpRequest('GET', '/api/messages/last');
        $this->router->dispatch(new ApiRequest($request), new ApiResponse($this->response));
        $this->assertMessageListResponse($expectedMessages);
    }

    public function testNextMessages(): void
    {
        $expectedMessages = array_slice($this->fillChat(), 3);
        $request = new StubHttpRequest('GET', '/api/messages/next', ['id' => 3]);
        $this->router->dispatch(new ApiRequest($request), new ApiResponse($this->response));
        $this->assertMessageListResponse($expectedMessages);
    }

    public function testPreviousMessages(): void
    {
        $expectedMessages = array_slice($this->fillChat(), 0, 2);
        $request = new StubHttpRequest('GET', '/api/messages/previous', ['id' => 3]);
        $this->router->dispatch(new ApiRequest($request), new ApiResponse($this->response));
        $this->assertMessageListResponse($expectedMessages);
    }
}

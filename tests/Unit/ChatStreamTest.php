<?php

namespace Tests\Antonowano\Chat\Unit;

use Antonowano\Chat\Chat;
use Antonowano\Chat\Message;
use Antonowano\Chat\Stream\StreamController;
use Antonowano\Chat\Stream\StreamFrame;
use Antonowano\Chat\Stream\StreamResponse;
use Antonowano\Chat\Stream\StreamRouter;
use Antonowano\Chat\Stream\WsResponse;
use Antonowano\Chat\Stubs\StubWsFrame;
use Antonowano\Chat\Stubs\StubWsResponse;
use Symfony\Component\Clock\MockClock;

class ChatStreamTest extends TestCase
{
    private MockClock $clock;
    private Chat $chat;
    private StreamRouter $router;
    private WsResponse $response;

    protected function setUp(): void
    {
        parent::setUp();
        $this->clock = new MockClock();
        $this->chat = $this->createChat([], $this->clock);
        $controller = new StreamController($this->chat);
        $this->router = new StreamRouter($controller);
        $this->response = new StubWsResponse();
    }

    /**
     * @param list<Message> $expectedMessages
     */
    protected function assertMessageListResponse(string $type, array $expectedMessages): void
    {
        $this->assertSame(
            [
                'type' => $type,
                'data' => array_map(fn ($message) => $message->toChatPayload(), $expectedMessages),
            ],
            $this->response->data()
        );
    }

    public function testSendMessage(): void
    {
        $frame = new StubWsFrame([
            'type' => 'NewMessage',
            'data' => [
                'text' => 'Hello World!',
                'author' => 'John Doe',
            ],
        ]);
        $this->router->dispatch(new StreamFrame($frame), new StreamResponse($this->response));

        $this->assertObjectListEquals(
            [
                $this->createMessage(1, 'Hello World!', $this->clock->now(), 'John Doe'),
            ],
            $this->chat->getLastMessages(10)
        );
    }

    public function testLastMessages(): void
    {
        $expectedMessages = $this->fillChat($this->chat, $this->clock);
        $frame = new StubWsFrame([
            'type' => 'LastMessages',
        ]);
        $this->router->dispatch(new StreamFrame($frame), new StreamResponse($this->response));
        $this->assertMessageListResponse('LastMessages', $expectedMessages);
    }

    public function testNextMessages(): void
    {
        $expectedMessages = array_slice($this->fillChat($this->chat, $this->clock), 3);
        $frame = new StubWsFrame([
            'type' => 'NextMessages',
            'data' => [
                'id' => 3,
            ]
        ]);
        $this->router->dispatch(new StreamFrame($frame), new StreamResponse($this->response));
        $this->assertMessageListResponse('NextMessages', $expectedMessages);
    }

    public function testPreviousMessages(): void
    {
        $expectedMessages = array_slice($this->fillChat($this->chat, $this->clock), 0, 2);
        $frame = new StubWsFrame([
            'type' => 'PreviousMessages',
            'data' => [
                'id' => 3,
            ]
        ]);
        $this->router->dispatch(new StreamFrame($frame), new StreamResponse($this->response));
        $this->assertMessageListResponse('PreviousMessages', $expectedMessages);
    }
}

<?php

use Antonowano\Chat\Chat;
use Antonowano\Chat\Stubs\StubWsFrame;
use Antonowano\Chat\Stubs\StubWsResponse;
use Antonowano\Chat\Swoole\SwooleWsChatListener;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Clock\MockClock;

beforeEach(function (): void {
    $this->chat = new Chat(new MockClock());
    $this->events = $this->chat->events();
    $this->messageStorage = $this->chat->messageStorage();
    $this->userStorage = $this->chat->userStorage();
    $this->roomStorage = $this->chat->roomStorage();
    $this->router = $this->chat->streamRouter();
    $this->user = $this->userStorage->create(createNewUser(name: 'John Doe'));
    $this->user2 = $this->userStorage->create(createNewUser());
    $this->user3 = $this->userStorage->create(createNewUser());
    $this->room = $this->roomStorage->create(createNewRoom(
        members: [$this->user, $this->user2],
    ));
    $this->room2 = $this->roomStorage->create(createNewRoom(
        members: [$this->user],
    ));
    $this->userListener = new StubWsResponse();
    $this->userListener2 = new StubWsResponse();
    $this->userListener3 = new StubWsResponse();
    $this->events->addListener('listener1', new SwooleWsChatListener($this->userListener, $this->user));
    $this->events->addListener('listener2', new SwooleWsChatListener($this->userListener2, $this->user2));
    $this->events->addListener('listener3', new SwooleWsChatListener($this->userListener3, $this->user3));
    $this->correlationId = Uuid::uuid4()->toString();
    $this->frame = new StubWsFrame([
        'correlationId' => $this->correlationId,
        'type' => 'NewMessage',
        'data' => [
            'roomId' => $this->room->id(),
            'text' => 'Hello World!',
        ],
    ]);
});

it('should return success when the message is created', function (): void {
    $response = sendRequestToWs($this->router, $this->frame, $this->user);
    expect($response->data())->toBe([
        'correlationId' => $this->correlationId,
        'status' => 'Success',
    ]);
});

it('should notify listeners of a new message', function (): void {
    sendRequestToWs($this->router, $this->frame, $this->user);
    expect($this->userListener)->data()
        ->toHaveKey('type', 'Message')
        ->toHaveKey('data.roomId', $this->room->id())
        ->toHaveKey('data.text', 'Hello World!')
        ->toHaveKey('data.author.name', 'John Doe')
        ->and($this->userListener2)->data()->toBe($this->userListener->data())
        ->and($this->userListener3)->data()->toBe([]);
});

it('should not send messages when the user is not a member', function (): void {
    $otherUser = $this->userStorage->create(createNewUser(name: 'Ivan'));
    sendRequestToWs($this->router, $this->frame, $otherUser);
    $messages = $this->messageStorage->getLastMessages($this->room->id(), 10);
    expect($messages)->toHaveCount(0);
});

it('should return an error if the user is not a member', function (): void {
    $otherUser = $this->userStorage->create(createNewUser());
    $response = sendRequestToWs($this->router, $this->frame, $otherUser);
    $data = $response->data();
    expect($data['status'])->toBe('Failure')
        ->and($data['correlationId'])->toBe($this->correlationId)
        ->and($data['data'])->toBeString()->not->toBeEmpty();
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
    $messages = $this->messageStorage->getLastMessages($this->room2->id(), 10);
    expect($messages)->toHaveCount(0);
});

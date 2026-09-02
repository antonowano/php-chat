<?php

use Antonowano\Chat\Chat;
use Antonowano\Chat\Stubs\StubWsFrame;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Clock\MockClock;

beforeEach(function (): void {
    $this->chat = new Chat(new MockClock());
    $this->messageStorage = $this->chat->messageStorage();
    $this->userStorage = $this->chat->userStorage();
    $this->roomStorage = $this->chat->roomStorage();
    $this->router = $this->chat->streamRouter();
    $this->user = $this->userStorage->create(createNewUser(name: 'John Doe'));
    $this->room = $this->roomStorage->create(createNewRoom(
        memberIds: [$this->user->id()],
    ));
    $this->room2 = $this->roomStorage->create(createNewRoom(
        memberIds: [$this->user->id()],
    ));
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

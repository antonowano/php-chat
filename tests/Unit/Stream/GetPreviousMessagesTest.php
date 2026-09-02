<?php

use Antonowano\Chat\Chat;
use Antonowano\Chat\Message;
use Antonowano\Chat\Stubs\StubWsFrame;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Clock\MockClock;

$roomId = 1;
$beforeId = 3;
$limit = 30;

beforeEach(function () use ($beforeId, $roomId): void {
    $this->clock = new MockClock();
    $this->chat = new Chat($this->clock);
    $messageStorage = $this->chat->messageStorage();
    $this->userStorage = $this->chat->userStorage();
    $roomStorage = $this->chat->roomStorage();
    $this->router = $this->chat->streamRouter();
    $this->messages = createFullChat($this->userStorage, $roomStorage, $messageStorage);
    $this->user = $this->userStorage->findAllById([1])[0];
    $this->correlationId = Uuid::uuid4()->toString();
    $this->frame = new StubWsFrame([
        'correlationId' => $this->correlationId,
        'type' => 'PreviousMessages',
        'data' => [
            'roomId' => $roomId,
            'id' => $beforeId,
        ]
    ]);
});

it(
    "should return {$limit} messages with an ID less than {$beforeId}",
    function () use ($limit, $roomId, $beforeId): void {
        $response = sendRequestToWs($this->router, $this->frame, $this->user);
        $expectedMessages = array_filter(
            $this->messages,
            fn (Message $m): bool => $m->roomId() === $roomId && $m->id() < $beforeId
        );
        $expectedMessages = array_slice($expectedMessages, -$limit);
        expect($response->data())->toBe([
            'correlationId' => $this->correlationId,
            'status' => 'Success',
            'data' => payloadOfMessages($expectedMessages),
        ]);
    }
);

it('should return an error if the user is not a member', function () use ($limit, $roomId): void {
    $otherUser = $this->userStorage->create(createNewUser());
    $response = sendRequestToWs($this->router, $this->frame, $otherUser);
    $data = $response->data();
    expect($data['status'])->toBe('Failure')
        ->and($data['correlationId'])->toBe($this->correlationId)
        ->and($data['data'])->toBeString()->not->toBeEmpty();
});

<?php

use Antonowano\Chat\Chat;
use Antonowano\Chat\Message;
use Antonowano\Chat\Stubs\StubWsFrame;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Clock\MockClock;

$roomId = 1;
$afterId = 3;
$limit = 30;

beforeEach(function () use ($afterId, $roomId): void {
    $this->chat = new Chat(new MockClock());
    $messageStorage = $this->chat->messageStorage();
    $this->userStorage = $this->chat->userStorage();
    $roomStorage = $this->chat->roomStorage();
    $this->router = $this->chat->streamRouter();
    $this->messages = createFullChat($this->userStorage, $roomStorage, $messageStorage);
    $this->user = $this->userStorage->findAllById([1])[0];
    $this->correlationId = Uuid::uuid4()->toString();
    $this->frame = new StubWsFrame([
        'correlationId' => $this->correlationId,
        'type' => 'NextMessages',
        'data' => [
            'roomId' => $roomId,
            'id' => $afterId,
        ]
    ]);
});

it(
    "should return {$limit} messages with an ID greater than {$afterId}",
    function () use ($limit, $roomId, $afterId): void {
        $response = sendRequestToWs($this->router, $this->frame, $this->user);
        $expectedMessages = array_filter(
            $this->messages,
            fn (Message $m): bool => $m->roomId() === $roomId && $m->id() > $afterId
        );
        $expectedMessages = array_slice($expectedMessages, 0, $limit);
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

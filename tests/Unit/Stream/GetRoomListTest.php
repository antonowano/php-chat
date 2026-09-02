<?php

use Antonowano\Chat\Chat;
use Antonowano\Chat\Room;
use Antonowano\Chat\Stubs\StubWsFrame;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Clock\MockClock;

$limit = 3;
$offset = 1;

beforeEach(function () use ($offset, $limit): void {
    $this->chat = new Chat(new MockClock());
    $userStorage = $this->chat->userStorage();
    $roomStorage = $this->chat->roomStorage();
    $this->router = $this->chat->streamRouter();
    $this->user = $userStorage->create(createNewUser());
    $this->rooms = [
        $roomStorage->create(createNewRoom()),
        $roomStorage->create(createNewRoom([$this->user->id()])),
        $roomStorage->create(createNewRoom([$this->user->id()])),
        $roomStorage->create(createNewRoom([$this->user->id()])),
        $roomStorage->create(createNewRoom([$this->user->id()])),
        $roomStorage->create(createNewRoom([$this->user->id()])),
        $roomStorage->create(createNewRoom()),
    ];
    $this->correlationId = Uuid::uuid4()->toString();
    $this->frame = new StubWsFrame([
        'correlationId' => $this->correlationId,
        'type' => 'RoomList',
        'data' => [
            'offset' => $offset,
            'limit' => $limit,
        ],
    ]);
});

it('should return a list of the user\'s rooms', function () use ($offset, $limit): void {
    $this->response = sendRequestToWs($this->router, $this->frame, $this->user);
    $expectedRooms = array_filter($this->rooms, fn (Room $m): bool => $m->hasMember($this->user));
    $expectedRooms = array_slice($expectedRooms, $offset, $limit);
    expect($this->response->data())->toBe([
        'correlationId' => $this->correlationId,
        'status' => 'Success',
        'data' => payloadOfRooms($expectedRooms),
    ]);
});

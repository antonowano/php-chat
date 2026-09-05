<?php

use Antonowano\Chat\Api\ApiRequest;
use Antonowano\Chat\Api\ApiResponse;
use Antonowano\Chat\Api\ApiRouter;
use Antonowano\Chat\Api\HttpRequest;
use Antonowano\Chat\Message;
use Antonowano\Chat\MessageStorage;
use Antonowano\Chat\NewMessage;
use Antonowano\Chat\NewRoom;
use Antonowano\Chat\NewUser;
use Antonowano\Chat\Role;
use Antonowano\Chat\Room;
use Antonowano\Chat\RoomStorage;
use Antonowano\Chat\Stream\StreamFrame;
use Antonowano\Chat\Stream\StreamResponse;
use Antonowano\Chat\Stream\StreamRouter;
use Antonowano\Chat\Stream\WsFrame;
use Antonowano\Chat\Stubs\StubHttpResponse;
use Antonowano\Chat\Stubs\StubWsResponse;
use Antonowano\Chat\User;
use Antonowano\Chat\UserStorage;

require_once __DIR__ . '/../vendor/autoload.php';

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind different classes or traits.
|
*/

//pest()->extend(TestCase::class)->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function sendRequestToApi(ApiRouter $router, HttpRequest $request, ?User $user = null): StubHttpResponse
{
    $response = new StubHttpResponse();
    $router->dispatch(new ApiRequest($request, $user ?? createUser()), new ApiResponse($response));
    return $response;
}

function sendRequestToWs(StreamRouter $router, WsFrame $frame, ?User $user = null): StubWsResponse
{
    $response = new StubWsResponse();
    $router->dispatch(new StreamFrame($frame, $user ?? createUser()), new StreamResponse($response));
    return $response;
}

/**
 * @param list<Message> $messages
 */
function payloadOfMessages(array $messages): array
{
    return array_map(fn($m) => $m->toChatPayload(), $messages);
}

/**
 * @param list<Room> $rooms
 */
function payloadOfRooms(array $rooms): array
{
    return array_map(fn($r) => $r->toChatPayload(), $rooms);
}

function createUser(int $id = 0, string $name = 'User', Role $role = Role::USER, string $accessToken = ''): User
{
    return new User(
        id: $id,
        name: $name,
        role: $role,
        accessToken: $accessToken,
    );
}

function createNewUser(string $name = 'User', Role $role = Role::USER): NewUser
{
    return new NewUser(
        name: $name,
        role: $role,
    );
}

function createMessage(
    int $id = 0,
    string $text = 'Text message',
    ?\DateTimeInterface $createdAt = null,
    ?Room $room = null,
    ?User $author = null,
): Message {
    return new Message(
        room: $room ?? createRoom(),
        id: $id,
        text: $text,
        createdAt: $createdAt ?? new DateTime('now'),
        author: $author ?? createUser(),
    );
}

function createNewMessage(Room $room, string $text, ?User $author = null): NewMessage
{
    return new NewMessage(
        room: $room,
        text: $text,
        author: $author ?? createUser(),
    );
}

/**
 * @param list<User> $members
 */
function createRoom(int $id = 0, array $members = []): Room
{
    return new Room(
        id: $id,
        members: $members,
    );
}

/**
 * @param list<User> $members
 */
function createNewRoom(array $members = []): NewRoom
{
    return new NewRoom(
        members: $members,
    );
}

/**
 * @return list<Message>
 */
function createFullChat(UserStorage $userStorage, RoomStorage $roomStorage, MessageStorage $messageStorage): array
{
    $ivan = $userStorage->create(createNewUser(name: 'Ivan'));
    $olga = $userStorage->create(createNewUser(name: 'Olga'));
    $john = $userStorage->create(createNewUser(name: 'John Doe'));
    $room1 = $roomStorage->create(createNewRoom(members: [$ivan, $olga]));
    $room2 = $roomStorage->create(createNewRoom(members: [$john]));
    $messages = [];

    foreach (range(1, 70) as $i) {
        if ($i % 3) {
            $room = $room2;
            $author = $john;
        } else {
            $room = $room1;
            $author = ($i % 2) ? $ivan : $olga;
        }

        $messages[] = $messageStorage->create(createNewMessage(
            room: $room,
            text: 'test message ' . $i,
            author: $author,
        ));
    }

    return $messages;
}

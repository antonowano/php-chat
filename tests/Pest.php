<?php

use Antonowano\Chat\Api\ApiRequest;
use Antonowano\Chat\Api\ApiResponse;
use Antonowano\Chat\Api\ApiRouter;
use Antonowano\Chat\Api\HttpRequest;
use Antonowano\Chat\Message;
use Antonowano\Chat\NewMessage;
use Antonowano\Chat\Role;
use Antonowano\Chat\Room;
use Antonowano\Chat\Stream\StreamFrame;
use Antonowano\Chat\Stream\StreamResponse;
use Antonowano\Chat\Stream\StreamRouter;
use Antonowano\Chat\Stream\WsFrame;
use Antonowano\Chat\Stubs\StubHttpResponse;
use Antonowano\Chat\Stubs\StubWsResponse;
use Antonowano\Chat\User;

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

function createUser(int $id = 0, string $name = 'User', Role $role = Role::USER): User
{
    return new User(
        id: $id,
        name: $name,
        role: $role,
    );
}

function createMessage(
    int $id = 0,
    string $text = 'Text message',
    ?\DateTimeInterface $createdAt = null,
    int $roomId = 0,
    ?User $author = null,
): Message {
    return new Message(
        roomId: $roomId,
        id: $id,
        text: $text,
        createdAt: $createdAt ?? new DateTime('now'),
        author: $author ?? createUser(),
    );
}

function createNewMessage(int $roomId, string $text, ?User $author = null): NewMessage
{
    return new NewMessage(
        roomId: $roomId,
        text: $text,
        author: $author ?? createUser(),
    );
}

function createRoom(int $id = 0, array $memberIds = []): Room
{
    return new Room(
        id: $id,
        memberIds: $memberIds,
    );
}

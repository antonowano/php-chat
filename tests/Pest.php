<?php

use Antonowano\Chat\Chat;
use Antonowano\Chat\Message;
use Antonowano\Chat\NewMessage;
use Antonowano\Chat\NewUser;
use Antonowano\Chat\Role;
use Antonowano\Chat\User;
use Psr\Clock\ClockInterface;
use Symfony\Component\Clock\MockClock;

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

function createNewUser(string $name = 'User', Role $role = Role::USER): NewUser
{
    return new NewUser(
        name: $name,
        role: $role,
    );
}

function createChat(?ClockInterface $clock = null): Chat
{
    return new Chat(
        clock: $clock ?? new MockClock(),
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

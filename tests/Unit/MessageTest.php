<?php

use Tests\Antonowano\Chat\Unit\TestCase;

uses(TestCase::class);

test('to string', function (): void {
    $message = createMessage(id: 15432, createdAt: new DateTime('2026-08-05 21:22:13'));

    expect((string) $message)->toBe('[21:22:13 05.08.2026] [#15432] User: Text message');
});

test('object equals', function (): void {
    $message1 = createMessage(id: 10, createdAt: new DateTime('2026-08-07 21:00:00'));
    $message2 = createMessage(id: 10, createdAt: new DateTime('2026-08-07 21:00:00'));

    $this->assertObjectEquals($message1, $message2);
});

test('object not equals', function (): void {
    $message1 = createMessage(id: 10);
    $message2 = createMessage(id: 11);

    $this->assertObjectNotEquals($message1, $message2);
});

test('has id less than returns true when message id is less', function (): void {
    $message = createMessage(id: 10);

    expect($message->hasIdLessThan(11))->toBeTrue()
        ->and($message->hasIdLessThan(100))->toBeTrue();
});

test('has id less than returns false when message id is equal', function (): void {
    $message = createMessage(id: 10);

    expect($message->hasIdLessThan(10))->toBeFalse();
});

test('has id less than returns false when message id is greater', function (): void {
    $message = createMessage(id: 10);

    expect($message->hasIdLessThan(9))->toBeFalse()
        ->and($message->hasIdLessThan(1))->toBeFalse()
        ->and($message->hasIdLessThan(-10))->toBeFalse();
});

test('has id greater than returns true when message id is greater', function (): void {
    $message = createMessage(id: 10);

    expect($message->hasIdGreaterThan(5))->toBeTrue()
        ->and($message->hasIdGreaterThan(9))->toBeTrue()
        ->and($message->hasIdGreaterThan(-100))->toBeTrue();
});

test('has id greater than returns false when message id is equal', function (): void {
    $message = createMessage(id: 10);

    expect($message->hasIdGreaterThan(10))->toBeFalse();
});

test('has id greater than returns false when message id is less', function (): void {
    $message = createMessage(id: 10);

    expect($message->hasIdGreaterThan(15))->toBeFalse()
        ->and($message->hasIdGreaterThan(100))->toBeFalse();
});

test('to chat payload', function (): void {
    $message = createMessage(
        id: 11,
        text: 'Hello World',
        createdAt: new DateTime('2027-08-05 21:22:13'),
        chatId: 5,
        author: createUser('Ivan'),
    );

    expect($message->toChatPayload())->toBe([
        'chatId' => 5,
        'id' => 11,
        'text' => 'Hello World',
        'author' => 'Ivan',
        'date' => '05.08.2027',
        'time' => '21:22',
    ]);
});

<?php

use Antonowano\Chat\AccessControl;
use Antonowano\Chat\Role;

beforeEach(function (): void {
    $this->accessControl = new AccessControl();
});

describe('Admin User', function (): void {
    beforeEach(function (): void {
        $this->user = createUser(role: Role::ADMIN);
    });

    it('can register users', function (): void {
        expect($this->accessControl->isGranted($this->user, 'user.register'))->toBeTrue();
    });
    it('can read any chats', function (): void {
        $chat = createChat();
        expect($this->accessControl->isGranted($this->user, 'chat.read', $chat))->toBeTrue();
    });
    it('can write to any chat', function (): void {
        $chat = createChat();
        expect($this->accessControl->isGranted($this->user, 'chat.read', $chat))->toBeTrue();
    });
});

describe('Standard User', function (): void {
    beforeEach(function (): void {
        $this->room = createRoom(1, [1, 2]);
    });

    it('cannot register users', function (): void {
        $user = createUser();
        expect($this->accessControl->isGranted($user, 'user.register'))->toBeFalse();
    });

    it('can read chats it is part of', function (): void {
        $user = createUser(id: 1);
        expect($this->accessControl->isGranted($user, 'room.read', $this->room))->toBeTrue();
    });

    it('cannot read chats it is not part of', function (): void {
        $user = createUser();
        expect($this->accessControl->isGranted($user, 'room.read', $this->room))->toBeFalse();
    });

    it('can write to chats it is part of', function (): void {
        $user = createUser(id: 2);
        expect($this->accessControl->isGranted($user, 'room.write', $this->room))->toBeTrue();
    });

    it('cannot write chats it is not part of', function (): void {
        $user = createUser();
        expect($this->accessControl->isGranted($user, 'room.write', $this->room))->toBeFalse();
    });
});

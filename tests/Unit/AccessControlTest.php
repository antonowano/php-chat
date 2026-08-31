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
        $room = createRoom();
        expect($this->accessControl->isGranted($this->user, 'room.read', $room))->toBeTrue();
    });
    it('can write to any chat', function (): void {
        $room = createRoom();
        expect($this->accessControl->isGranted($this->user, 'room.write', $room))->toBeTrue();
    });
});

describe('Standard User', function (): void {
    beforeEach(function (): void {
        $this->user1 = createUser(id: 1);
        $this->user2 = createUser(id: 2);
        $this->user3 = createUser(id: 3);
        $this->room = createRoom(1, [$this->user1, $this->user2]);
    });

    it('cannot register users', function (): void {
        $user = createUser();
        expect($this->accessControl->isGranted($user, 'user.register'))->toBeFalse();
    });

    it('can read chats it is part of', function (): void {
        expect($this->accessControl->isGranted($this->user1, 'room.read', $this->room))->toBeTrue();
    });

    it('cannot read chats it is not part of', function (): void {
        expect($this->accessControl->isGranted($this->user3, 'room.read', $this->room))->toBeFalse();
    });

    it('can write to chats it is part of', function (): void {
        expect($this->accessControl->isGranted($this->user2, 'room.write', $this->room))->toBeTrue();
    });

    it('cannot write chats it is not part of', function (): void {
        expect($this->accessControl->isGranted($this->user3, 'room.write', $this->room))->toBeFalse();
    });
});

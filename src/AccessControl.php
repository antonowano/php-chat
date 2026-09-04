<?php

namespace Antonowano\Chat;

readonly class AccessControl
{
    public function isGranted(User $user, string $permission, mixed $subject = null): bool
    {
        if ($user->role() === Role::ADMIN) {
            return true;
        }

        if (in_array($permission, ['room.write', 'room.read']) && $subject instanceof Room) {
            return $subject->hasMember($user);
        }

        return false;
    }
}

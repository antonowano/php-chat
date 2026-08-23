<?php

namespace Antonowano\Chat;

class AccessControl
{
    public function isGranted(User $user, string $permission, mixed $subject = null): bool
    {
        if ($user->role() === Role::ADMIN) {
            return true;
        }

        if (str_starts_with($permission, 'room.') && $subject instanceof Room) {
            return $subject->hasMember($user);
        }

        return false;
    }
}

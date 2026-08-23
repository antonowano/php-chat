<?php

namespace Antonowano\Chat;

class AccessControl
{
    public function isGranted(User $user, string $permission, mixed $subject = null): bool
    {
        if ($user->role() === Role::ADMIN) {
            return true;
        }

        if (str_starts_with($permission, 'chat.') && $subject instanceof Chat) {
            // TODO this is a stub. The implementation of rooms is required to continue.
            return $user->id() === 1;
        }

        return false;
    }
}

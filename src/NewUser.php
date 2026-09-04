<?php

namespace Antonowano\Chat;

use Antonowano\Chat\Api\ApiRequest;

readonly class NewUser
{
    public function __construct(
        private string $name,
        private Role $role,
    ) {
    }

    public static function createFromApiRequest(ApiRequest $apiRequest): NewUser
    {
        $data = $apiRequest->json();
        return new NewUser(
            name: $data->get('name'),
            role: Role::USER,
        );
    }

    public function name(): string
    {
        return $this->name;
    }

    public function role(): Role
    {
        return $this->role;
    }
}

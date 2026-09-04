<?php

namespace Antonowano\Chat;

use Antonowano\Chat\Api\ApiRequest;

readonly class NewRoom
{
    public function __construct(
        /** @var list<int> */
        private array $memberIds,
    ) {
    }

    public static function createFromApiRequest(ApiRequest $apiRequest): NewRoom
    {
        $data = $apiRequest->json();
        return new NewRoom(
            memberIds: $data->get('memberIds'),
        );
    }

    /**
     * @return list<int>
     */
    public function memberIds(): array
    {
        return $this->memberIds;
    }
}

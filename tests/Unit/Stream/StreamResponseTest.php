<?php

namespace Tests\Antonowano\Chat\Unit\Stream;

use Antonowano\Chat\Stream\StreamResponse;
use Antonowano\Chat\Stream\WsResponse;
use Tests\Antonowano\Chat\Unit\TestCase;

class StreamResponseTest extends TestCase
{
    public function testSendMessageList(): void
    {
        $messages = $this->createMessages([1, 2]);
        $wsResponse = $this->createMock(WsResponse::class);
        $wsResponse->expects($this->once())->method('push')->with(
            $this->callback(function (array $data) use (&$messages) {
                return $data['type'] === 'LastMessages'
                    && count($data['data']) === 2
                    && $data['data'][0] === $messages[0]->toChatPayload()
                    && $data['data'][1] === $messages[1]->toChatPayload();
            }),
        );
        $response = new StreamResponse($wsResponse);
        $response->sendMessageList($messages);
    }
}

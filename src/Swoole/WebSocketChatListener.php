<?php

namespace Antonowano\Chat\Swoole;

use Antonowano\Chat\ChatListener;
use Antonowano\Chat\Message;
use OpenSwoole\WebSocket\Server;

readonly class WebSocketChatListener implements ChatListener
{
    public function __construct(
        private Server $server,
        private int $fd,
    ) {
    }

    public static function generateId(int $fd): string
    {
        return 'fd' . $fd;
    }

    public function id(): string
    {
        return self::generateId($this->fd);
    }

    public function onMessageSent(Message $message): void
    {
        $this->server->push($this->fd, json_encode([
            'type' => 'Message',
            'message' => $message->toChatPayload(),
        ]));
    }
}

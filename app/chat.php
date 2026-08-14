<?php

error_reporting(E_ALL);

require_once __DIR__ . '/../vendor/autoload.php';

use Antonowano\Chat\Chat;
use Antonowano\Chat\Message;
use Antonowano\Chat\NewMessage;
use Antonowano\Chat\Swoole\ApiRequest;
use Antonowano\Chat\Swoole\WebSocketChatListener;
use Antonowano\Chat\Swoole\WsFrame;
use OpenSwoole\Http\Request;
use OpenSwoole\Http\Response;
use OpenSwoole\WebSocket\Frame;
use OpenSwoole\WebSocket\Server;
use Symfony\Component\Clock\NativeClock;

$server = new Server('0.0.0.0', 9501, SWOOLE_BASE);
$chat = new Chat(new NativeClock());

$server->on('Start', function (Server $server) {
    echo 'OpenSwoole http server is started' . PHP_EOL;
});

$server->on('Open', function (Server $server, Request $request) use ($chat) {
    echo "server: handshake success with fd{$request->fd}\n";
    $listener = new WebSocketChatListener($server, $request->fd);
    $chat->addListener($listener);
});

$server->on('Message', function (Server $server, Frame $frame) use ($chat) {
    $wsFrame = new WsFrame($frame);
    $data = $wsFrame->data();
    if ($data->get('type') == 'NewMessage') {
        $chat->sendMessage(new NewMessage(
            text: $data->get('newMessage.text'),
            author: $data->get('newMessage.author'),
        ));
    }
});

$server->on('Close', function (Server $server, int $fd) use ($chat) {
    echo "client {$fd} closed\n";
    $chat->removeListenerById(WebSocketChatListener::generateId($fd));
});

$server->on('Request', function (Request $request, Response $response) use ($chat, $server) {
    $response->header('Content-Type', 'application/json');
    $apiRequest = new ApiRequest($request);

    if ($apiRequest->isMethod('POST') && $apiRequest->isPath('/api/message/send')) {
        $data = $apiRequest->json();
        $chat->sendMessage(new NewMessage(
            text: $data->get('text'),
            author: $data->get('author'),
        ));
        $response->end(json_encode([
            'status' => 'Success',
        ]));
    } elseif ($apiRequest->isPath('/api/messages/last')) {
        $messages = $chat->getLastMessages(30);
        $data = array_map(fn (Message $message) => $message->toChatPayload(), $messages);
        $response->end(json_encode([
            'status' => 'Success',
            'messages' => $data,
        ]));
    } elseif ($apiRequest->isPath('/api/messages/next')) {
        $afterId = $apiRequest->query()->get('id', 0);
        $messages = $chat->getMessagesAfterId($afterId, 30);
        $data = array_map(fn (Message $message) => $message->toChatPayload(), $messages);
        $response->end(json_encode([
            'status' => 'Success',
            'messages' => $data,
        ]));
    } elseif ($apiRequest->isPath('/api/messages/previous')) {
        $beforeId = $apiRequest->query()->get('id', 0);
        $messages = $chat->getMessagesBeforeId($beforeId, 30);
        $data = array_map(fn (Message $message) => $message->toChatPayload(), $messages);
        $response->end(json_encode([
            'status' => 'Success',
            'messages' => $data,
        ]));
    } else {
        $response->status(404);
        $response->end(json_encode([
            'status' => 'NotFound',
            'message' => 'Route not found',
        ]));
    }
});

$server->start();

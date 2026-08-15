<?php

error_reporting(E_ALL);

require_once __DIR__ . '/../vendor/autoload.php';

use Antonowano\Chat\Chat;
use Antonowano\Chat\NewMessage;
use Antonowano\Chat\ApiResponse;
use Antonowano\Chat\Swoole\ApiRequest;
use Antonowano\Chat\Swoole\SwooleHttpResponse;
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

$server->on('Request', function (Request $swooleRequest, Response $swooleResponse) use ($chat, $server) {
    $request = new ApiRequest($swooleRequest);
    $response = new ApiResponse(new SwooleHttpResponse($swooleResponse));

    if ($request->isMethod('POST') && $request->isPath('/api/message/send')) {
        $data = $request->json();
        $chat->sendMessage(new NewMessage(
            text: $data->get('text'),
            author: $data->get('author'),
        ));
        $response->sendCreated();
    } elseif ($request->isPath('/api/messages/last')) {
        $messages = $chat->getLastMessages(30);
        $response->sendMessageList($messages);
    } elseif ($request->isPath('/api/messages/next')) {
        $afterId = $request->query()->get('id', 0);
        $messages = $chat->getMessagesAfterId($afterId, 30);
        $response->sendMessageList($messages);
    } elseif ($request->isPath('/api/messages/previous')) {
        $beforeId = $request->query()->get('id', 0);
        $messages = $chat->getMessagesBeforeId($beforeId, 30);
        $response->sendMessageList($messages);
    } else {
        $response->sendRouteNotFound();
    }
});

$server->start();

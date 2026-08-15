<?php

error_reporting(E_ALL);

require_once __DIR__ . '/../vendor/autoload.php';

use Antonowano\Chat\ApiRequest;
use Antonowano\Chat\Chat;
use Antonowano\Chat\HttpMethod;
use Antonowano\Chat\HttpPath;
use Antonowano\Chat\NewMessage;
use Antonowano\Chat\ApiResponse;
use Antonowano\Chat\Swoole\SwooleHttpRequest;
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
    $request = new ApiRequest(new SwooleHttpRequest($swooleRequest));
    $response = new ApiResponse(new SwooleHttpResponse($swooleResponse));

    if ($request->routeMatches(HttpPath::SEND_MESSAGE, HttpMethod::POST)) {
        $data = $request->json();
        $chat->sendMessage(new NewMessage(
            text: $data->get('text'),
            author: $data->get('author'),
        ));
        $response->sendCreated();
    } elseif ($request->routeMatches(HttpPath::LAST_MESSAGES, HttpMethod::GET)) {
        $messages = $chat->getLastMessages(30);
        $response->sendMessageList($messages);
    } elseif ($request->routeMatches(HttpPath::NEXT_MESSAGES, HttpMethod::GET)) {
        $afterId = $request->query()->get('id', 0);
        $messages = $chat->getMessagesAfterId($afterId, 30);
        $response->sendMessageList($messages);
    } elseif ($request->routeMatches(HttpPath::PREVIOUS_MESSAGES, HttpMethod::GET)) {
        $beforeId = $request->query()->get('id', 0);
        $messages = $chat->getMessagesBeforeId($beforeId, 30);
        $response->sendMessageList($messages);
    } else {
        $response->sendRouteNotFound();
    }
});

$server->start();

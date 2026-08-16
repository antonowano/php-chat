<?php

error_reporting(E_ALL);

require_once __DIR__ . '/../vendor/autoload.php';

use Antonowano\Chat\ApiController;
use Antonowano\Chat\ApiRequest;
use Antonowano\Chat\Chat;
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
$apiController = new ApiController($chat);

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
    if (!$wsFrame->finish()) {
        return;
    }
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

$server->on('Request', function (Request $swooleRequest, Response $swooleResponse) use ($apiController) {
    $request = new ApiRequest(new SwooleHttpRequest($swooleRequest));
    $response = new ApiResponse(new SwooleHttpResponse($swooleResponse));

    if ($request->routeMatches('/api/message/send', 'POST')) {
        $apiController->sendMessage($request, $response);
    } elseif ($request->routeMatches('/api/messages/last', 'GET')) {
        $apiController->lastMessages($request, $response);
    } elseif ($request->routeMatches('/api/messages/next', 'GET')) {
        $apiController->nextMessages($request, $response);
    } elseif ($request->routeMatches('/api/messages/previous', 'GET')) {
        $apiController->previousMessages($request, $response);
    } else {
        $response->sendRouteNotFound();
    }
});

$server->start();

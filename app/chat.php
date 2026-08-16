<?php

error_reporting(E_ALL);

require_once __DIR__ . '/../vendor/autoload.php';

use Antonowano\Chat\Api\ApiController;
use Antonowano\Chat\Api\ApiRequest;
use Antonowano\Chat\Api\ApiResponse;
use Antonowano\Chat\Api\ApiRoute;
use Antonowano\Chat\Api\ApiRouter;
use Antonowano\Chat\Chat;
use Antonowano\Chat\NewMessage;
use Antonowano\Chat\Swoole\SwooleHttpRequest;
use Antonowano\Chat\Swoole\SwooleHttpResponse;
use Antonowano\Chat\Swoole\SwooleWsChatListener;
use Antonowano\Chat\Swoole\SwooleWsFrame;
use OpenSwoole\Http\Request;
use OpenSwoole\Http\Response;
use OpenSwoole\WebSocket\Frame;
use OpenSwoole\WebSocket\Server;
use Symfony\Component\Clock\NativeClock;

$server = new Server('0.0.0.0', 9501, SWOOLE_BASE);
$chat = new Chat(new NativeClock());
$apiController = new ApiController($chat);
$apiRouter = new ApiRouter([
    new ApiRoute('POST', '/api/message/send', [$apiController, 'sendMessage']),
    new ApiRoute('GET', '/api/messages/last', [$apiController, 'lastMessages']),
    new ApiRoute('GET', '/api/messages/next', [$apiController, 'nextMessages']),
    new ApiRoute('GET', '/api/messages/previous', [$apiController, 'previousMessages']),
]);

$server->on('Start', function () {
    echo 'OpenSwoole http server is started' . PHP_EOL;
});

$server->on('Open', function (Server $server, Request $request) use ($chat) {
    echo "server: handshake success with fd{$request->fd}\n";
    $listener = new SwooleWsChatListener($server, $request->fd);
    $chat->addListener(SwooleWsChatListener::generateId($request->fd), $listener);
});

$server->on('Message', function (Server $server, Frame $frame) use ($chat) {
    $wsFrame = new SwooleWsFrame($frame);
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
    $chat->removeListenerById(SwooleWsChatListener::generateId($fd));
});

$server->on('Request', function (Request $rawRequest, Response $rawResponse) use ($apiRouter) {
    $apiRouter->dispatch(
        new ApiRequest(new SwooleHttpRequest($rawRequest)),
        new ApiResponse(new SwooleHttpResponse($rawResponse))
    );
});

$server->start();

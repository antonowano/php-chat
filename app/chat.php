<?php

error_reporting(E_ALL);

require_once __DIR__ . '/../vendor/autoload.php';

use Antonowano\Chat\Api\ApiController;
use Antonowano\Chat\Api\ApiRequest;
use Antonowano\Chat\Api\ApiResponse;
use Antonowano\Chat\Api\ApiRouter;
use Antonowano\Chat\Chat;
use Antonowano\Chat\SessionStorage;
use Antonowano\Chat\Stream\StreamController;
use Antonowano\Chat\Stream\StreamFrame;
use Antonowano\Chat\Stream\StreamResponse;
use Antonowano\Chat\Stream\StreamRouter;
use Antonowano\Chat\Swoole\SwooleHttpRequest;
use Antonowano\Chat\Swoole\SwooleHttpResponse;
use Antonowano\Chat\Swoole\SwooleWsChatListener;
use Antonowano\Chat\Swoole\SwooleWsFrame;
use Antonowano\Chat\Swoole\SwooleWsResponse;
use Antonowano\Chat\UserStorage;
use OpenSwoole\Http\Request;
use OpenSwoole\Http\Response;
use OpenSwoole\WebSocket\Frame;
use OpenSwoole\WebSocket\Server;
use Symfony\Component\Clock\NativeClock;

$server = new Server('0.0.0.0', 9501, SWOOLE_BASE);
$chat = new Chat(new NativeClock());
$userStorage = new UserStorage();
$sessionStorage = new SessionStorage();
$apiController = new ApiController($chat, $userStorage);
$apiRouter = new ApiRouter($apiController);
$streamController = new StreamController($chat);
$streamRouter = new StreamRouter($streamController);

echo 'Admin token: ' . $userStorage->register('Ivan') . PHP_EOL;

$server->on('Start', function (): void {
    echo 'OpenSwoole http server is started' . PHP_EOL;
});

$server->on('Handshake', function (Request $request, Response $response) use ($chat, $server, $userStorage, $sessionStorage): void {
    try {
        $secWebSocketKey = $request->header['sec-websocket-key'] ?? '';

        if (0 === preg_match('#^[+/0-9A-Za-z]{21}[AQgw]==$#', $secWebSocketKey)
            || 16 !== strlen(base64_decode($secWebSocketKey))) {
            $response->status(400);
            $response->end();
            return;
        }

        $apiRequest = new ApiRequest(new SwooleHttpRequest($request));
        $user = $userStorage->findNameByToken($apiRequest->accessToken());

        if (!$user) {
            $response->status(401);
            $response->end();
            return;
        }

        $key = base64_encode(sha1($secWebSocketKey . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11', true));

        $headers = [
            'Upgrade' => 'websocket',
            'Connection' => 'Upgrade',
            'Sec-WebSocket-Accept' => $key,
            'Sec-WebSocket-Version' => '13',
        ];

        if(isset($request->header['sec-websocket-protocol'])) {
            $headers['Sec-WebSocket-Protocol'] = $request->header['sec-websocket-protocol'];
        }

        foreach($headers as $key => $val) {
            $response->header($key, $val);
        }

        $response->status(101);
        $response->end();

        $server->defer(function () use ($chat, $server, $request, $user, $sessionStorage): void {
            echo "server: handshake success with fd{$request->fd}\n";
            $sessionStorage->add($request->fd, $user);
            $listener = new SwooleWsChatListener(new SwooleWsResponse($server, $request->fd));
            $chat->addListener(SwooleWsChatListener::generateId($request->fd), $listener);
        });
    } catch (Throwable $e) {
        echo $e . PHP_EOL;
    }
});

$server->on('Message', function (Server $server, Frame $rawFrame) use ($streamRouter, $sessionStorage): void {
    try {
        if (!$rawFrame->finish) {
            return;
        }
        $user = $sessionStorage->get($rawFrame->fd);
        $streamRouter->dispatch(
            new StreamFrame(new SwooleWsFrame($rawFrame), $user),
            new StreamResponse(new SwooleWsResponse($server, $rawFrame->fd))
        );
    } catch (Throwable $e) {
        echo $e . PHP_EOL;
    }
});

$server->on('Close', function (Server $server, int $fd) use ($chat, $sessionStorage): void {
    try {
        echo "client {$fd} closed\n";
        $sessionStorage->remove($fd);
        $chat->removeListenerById(SwooleWsChatListener::generateId($fd));
    } catch (Throwable $e) {
        echo $e . PHP_EOL;
    }
});

$server->on('Request', function (Request $rawRequest, Response $rawResponse) use ($apiRouter): void {
    try {
        $apiRouter->dispatch(
            new ApiRequest(new SwooleHttpRequest($rawRequest)),
            new ApiResponse(new SwooleHttpResponse($rawResponse))
        );
    } catch (Throwable $e) {
        echo $e . PHP_EOL;
    }
});

$server->start();

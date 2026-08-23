<?php

error_reporting(E_ALL);

require_once __DIR__ . '/../vendor/autoload.php';

use Antonowano\Chat\Api\ApiController;
use Antonowano\Chat\Api\ApiRequest;
use Antonowano\Chat\Api\ApiResponse;
use Antonowano\Chat\Api\ApiRouter;
use Antonowano\Chat\Events;
use Antonowano\Chat\MessageStorage;
use Antonowano\Chat\NewUser;
use Antonowano\Chat\Role;
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
$messageStorage = new MessageStorage(new NativeClock());
$events = new Events();
$userStorage = new UserStorage();
$sessionStorage = new SessionStorage();
$apiController = new ApiController($events, $userStorage, $messageStorage);
$apiRouter = new ApiRouter($apiController);
$streamController = new StreamController($events, $messageStorage);
$streamRouter = new StreamRouter($streamController);

echo 'Admin token: ' . $userStorage->register(new NewUser('Admin', Role::ADMIN)) . PHP_EOL;

$server->on('Start', function (): void {
    echo 'OpenSwoole http server is started' . PHP_EOL;
});

$server->on('Handshake', function (Request $request, Response $response) use ($events, $server, $userStorage, $sessionStorage): void {
    try {
        $secWebSocketKey = $request->header['sec-websocket-key'] ?? '';

        if (0 === preg_match('#^[+/0-9A-Za-z]{21}[AQgw]==$#', $secWebSocketKey)
            || 16 !== strlen(base64_decode($secWebSocketKey))) {
            $response->status(400);
            $response->end();
            return;
        }

        $swooleRequest = new SwooleHttpRequest($request);
        $user = $userStorage->findByToken($swooleRequest->bearerToken());

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

        $server->defer(function () use ($events, $server, $request, $user, $sessionStorage): void {
            echo "server: handshake success with fd{$request->fd}\n";
            $sessionStorage->add($request->fd, $user);
            $listener = new SwooleWsChatListener(new SwooleWsResponse($server, $request->fd));
            $events->addListener(SwooleWsChatListener::generateId($request->fd), $listener);
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

$server->on('Close', function (Server $server, int $fd) use ($events, $sessionStorage): void {
    try {
        echo "client {$fd} closed\n";
        $sessionStorage->remove($fd);
        $events->removeListenerById(SwooleWsChatListener::generateId($fd));
    } catch (Throwable $e) {
        echo $e . PHP_EOL;
    }
});

$server->on('Request', function (Request $rawRequest, Response $rawResponse) use ($apiRouter, $userStorage): void {
    try {
        $swooleRequest = new SwooleHttpRequest($rawRequest);
        $user = $userStorage->findByToken($swooleRequest->bearerToken());
        $apiRouter->dispatch(
            new ApiRequest($swooleRequest, $user),
            new ApiResponse(new SwooleHttpResponse($rawResponse))
        );
    } catch (Throwable $e) {
        echo $e . PHP_EOL;
    }
});

$server->start();

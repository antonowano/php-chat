<?php

error_reporting(E_ALL);

require_once __DIR__ . '/../vendor/autoload.php';

use Antonowano\Chat\Api\ApiRequest;
use Antonowano\Chat\Api\ApiResponse;
use Antonowano\Chat\Chat;
use Antonowano\Chat\NewUser;
use Antonowano\Chat\Role;
use Antonowano\Chat\Stream\StreamFrame;
use Antonowano\Chat\Stream\StreamResponse;
use Antonowano\Chat\Swoole\SwooleHttpRequest;
use Antonowano\Chat\Swoole\SwooleHttpResponse;
use Antonowano\Chat\Swoole\SwooleWsChatListener;
use Antonowano\Chat\Swoole\SwooleWsFrame;
use Antonowano\Chat\Swoole\SwooleWsResponse;
use OpenSwoole\Http\Request;
use OpenSwoole\Http\Response;
use OpenSwoole\WebSocket\Frame;
use OpenSwoole\WebSocket\Server;
use Symfony\Component\Clock\NativeClock;

$server = new Server('0.0.0.0', 9501, SWOOLE_BASE);
$chat = new Chat(new NativeClock());

echo 'Admin token: ' . $chat->userStorage()->create(new NewUser('Admin', Role::ADMIN))->accessToken() . PHP_EOL;

$server->on('Start', function (): void {
    echo 'OpenSwoole http server is started' . PHP_EOL;
});

$server->on('Handshake', function (Request $request, Response $response) use ($chat, $server): void {
    try {
        $secWebSocketKey = $request->header['sec-websocket-key'] ?? '';

        if (0 === preg_match('#^[+/0-9A-Za-z]{21}[AQgw]==$#', $secWebSocketKey)
            || 16 !== strlen(base64_decode($secWebSocketKey))) {
            $response->status(400);
            $response->end();
            return;
        }

        $swooleRequest = new SwooleHttpRequest($request);
        $user = $chat->userStorage()->findByToken($swooleRequest->bearerToken());

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

        $server->defer(function () use ($chat, $server, $request, $user): void {
            echo "server: handshake success with fd{$request->fd}\n";
            $chat->sessionStorage()->add($request->fd, $user);
            $listener = new SwooleWsChatListener(new SwooleWsResponse($server, $request->fd));
            $chat->events()->addListener(SwooleWsChatListener::generateId($request->fd), $listener);
        });
    } catch (Throwable $e) {
        echo $e . PHP_EOL;
    }
});

$server->on('Message', function (Server $server, Frame $rawFrame) use ($chat): void {
    try {
        if (!$rawFrame->finish) {
            return;
        }
        $user = $chat->sessionStorage()->get($rawFrame->fd);
        $chat->streamRouter()->dispatch(
            new StreamFrame(new SwooleWsFrame($rawFrame), $user),
            new StreamResponse(new SwooleWsResponse($server, $rawFrame->fd))
        );
    } catch (Throwable $e) {
        echo $e . PHP_EOL;
    }
});

$server->on('Close', function (Server $server, int $fd) use ($chat): void {
    try {
        echo "client {$fd} closed\n";
        $chat->sessionStorage()->remove($fd);
        $chat->events()->removeListenerById(SwooleWsChatListener::generateId($fd));
    } catch (Throwable $e) {
        echo $e . PHP_EOL;
    }
});

$server->on('Request', function (Request $rawRequest, Response $rawResponse) use ($chat): void {
    try {
        $swooleRequest = new SwooleHttpRequest($rawRequest);
        $user = $chat->userStorage()->findByToken($swooleRequest->bearerToken());
        $chat->apiRouter()->dispatch(
            new ApiRequest($swooleRequest, $user),
            new ApiResponse(new SwooleHttpResponse($rawResponse))
        );
    } catch (Throwable $e) {
        echo $e . PHP_EOL;
    }
});

$server->start();

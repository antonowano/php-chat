<?php

error_reporting(E_ALL);

require_once __DIR__ . '/../vendor/autoload.php';

use Antonowano\Chat\Chat;
use Antonowano\Chat\Message;
use Antonowano\Chat\Swoole\ApiRequest;
use OpenSwoole\Http\Server;
use OpenSwoole\Http\Request;
use OpenSwoole\Http\Response;

$server = new Server('0.0.0.0', 9501);
$chat = new Chat();
$messageId = 1;

$server->on('Start', function (Server $server) {
    echo 'OpenSwoole http server is started' . PHP_EOL;
});

$server->on('Request', function (Request $request, Response $response) use ($chat, &$messageId) {
    $response->header('Content-Type', 'text/plain');
    $apiRequest = new ApiRequest($request);

    if ($apiRequest->isMethod('POST') && $apiRequest->isPath('/api/message/send')) {
        $data = $apiRequest->json();
        $chat->sendMessage(new Message(
            id: $messageId++,
            text: $data->get('text'),
            createdAt: new \DateTime('now'),
            author: $data->get('author'),
        ));
        $response->end('Message sent');
    } elseif ($apiRequest->isPath('/api/messages/last')) {
        $response->end(var_export($chat->getLastMessages(30), true));
    } else {
        $response->status(404);
        $response->end('Not Found');
    }

    // debug Request
    // $response->end(
    //     var_export($request, true) . PHP_EOL
    //     . 'Methods: ' . var_export(get_class_methods($request), true) . PHP_EOL
    //     . 'Data: ' . var_export($request->getData(), true) . PHP_EOL
    //     . 'isCompleted: ' . var_export($request->isCompleted(), true) . PHP_EOL
    //     . 'Raw Content: ' . var_export($request->rawContent(), true) . PHP_EOL
    //     . 'Content: ' . var_export($request->getContent(), true) . PHP_EOL
    //     . 'Method: ' . var_export($request->getMethod(), true) . PHP_EOL
    //     . 'Request Uri: ' . var_export($request->server['request_uri'], true) . PHP_EOL
    // );
});

$server->start();

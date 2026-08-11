<?php

error_reporting(E_ALL);

require_once __DIR__ . '/../vendor/autoload.php';

use Antonowano\Chat\Chat;
use Antonowano\Chat\Message;
use Antonowano\Chat\NewMessage;
use Antonowano\Chat\Swoole\ApiRequest;
use OpenSwoole\Http\Server;
use OpenSwoole\Http\Request;
use OpenSwoole\Http\Response;
use Symfony\Component\Clock\NativeClock;

$server = new Server('0.0.0.0', 9501);
$clock = new NativeClock();
$chat = new Chat($clock);

$server->on('Start', function (Server $server) {
    echo 'OpenSwoole http server is started' . PHP_EOL;
});

$server->on('Request', function (Request $request, Response $response) use ($chat) {
    $response->header('Content-Type', 'application/json');
    $apiRequest = new ApiRequest($request);

    if ($apiRequest->isMethod('POST') && $apiRequest->isPath('/api/message/send')) {
        $data = $apiRequest->json();
        $chat->sendMessage(new NewMessage(
            text: $data->get('text'),
            author: $data->get('author'),
        ));
        $response->end('{"status": "Success"}');
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
    } elseif ($apiRequest->isPath('/api/messages/prev')) {
        $beforeId = $apiRequest->query()->get('id', 0);
        $messages = $chat->getMessagesBeforeId($beforeId, 30);
        $data = array_map(fn (Message $message) => $message->toChatPayload(), $messages);
        $response->end(json_encode([
            'status' => 'Success',
            'messages' => $data,
        ]));
    } else {
        $response->status(404);
        $response->end('{"status": "NotFound", "message": "Route not found"}');
        //$response->end(
        //    var_export($request, true) . PHP_EOL
        //    . 'Methods: ' . var_export(get_class_methods($request), true) . PHP_EOL
        //    . 'Data: ' . var_export($request->getData(), true) . PHP_EOL
        //    . 'isCompleted: ' . var_export($request->isCompleted(), true) . PHP_EOL
        //    . 'Raw Content: ' . var_export($request->rawContent(), true) . PHP_EOL
        //    . 'Content: ' . var_export($request->getContent(), true) . PHP_EOL
        //    . 'Method: ' . var_export($request->getMethod(), true) . PHP_EOL
        //    . 'Request Uri: ' . var_export($request->server['request_uri'], true) . PHP_EOL
        //);
    }
});

$server->start();

<?php

error_reporting(E_ALL);

require_once __DIR__ . '/../vendor/autoload.php';

use OpenSwoole\Http\Server;
use OpenSwoole\Http\Request;
use OpenSwoole\Http\Response;

$server = new Server('0.0.0.0', 9501);

$server->on('Start', function (Server $server) {
    echo 'OpenSwoole http server is started' . PHP_EOL;
});

$server->on('Request', function (Request $request, Response $response) {
    $response->header('Content-Type', 'text/plain');
    $response->end(var_export($request, true) . PHP_EOL);
});

$server->start();

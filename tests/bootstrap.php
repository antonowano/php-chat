<?php

namespace {
    require_once __DIR__ . '/../vendor/autoload.php';

    if (!defined('SWOOLE_BASE')) {
        define('SWOOLE_BASE', 1);
    }
}

namespace OpenSwoole\Http {
    if (!class_exists('OpenSwoole\Http\Request')) {
        class Request
        {
            public int $fd = 0;
            public array $server = [
                'request_uri' => '',
            ];

            public function getMethod(): string { return 'GET'; }

            public function getContent(): string { return ''; }
        }
    }
    if (!class_exists('OpenSwoole\Http\Response')) {
        class Response
        {
            public function header(string $name, string $value): void { }

            public function status(int $status): void { }

            public function end(string $content): void { }
        }
    }
}

namespace OpenSwoole\WebSocket {
    if (!class_exists('OpenSwoole\WebSocket\Server')) {
        class Server
        {
            public function __construct($host, $port = null, $mode = null, $sockType = null) {}

            public function on(string $eventName, callable $callback): void {}

            public function start(): void {}

            public function push($fd, $data, $opcode = null, $flags = null) {}

            public function isEstablished(int $fd): bool { return true; }
        }
    }
    if (!class_exists('OpenSwoole\WebSocket\Frame')) {
        class Frame
        {
            public int $fd = 0;
            public string $data = '';
            public int $opcode = 0;
            public bool $finish = true;
        }
    }
}

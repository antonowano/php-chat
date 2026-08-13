<?php

namespace {
    require_once __DIR__ . '/../vendor/autoload.php';
}

namespace OpenSwoole\Http {
    if (!class_exists('OpenSwoole\Http\Request')) {
        class Request {
            public int $fd = 0;
            public array $server = [];

            public function getMethod(): string { return 'GET'; }

            public function getContent(): string { return ''; }
        }
    }
}

namespace OpenSwoole\WebSocket {
    if (!class_exists('OpenSwoole\WebSocket\Frame')) {
        class Frame {
            public int $fd;
            public string $data;
            public int $opcode;
            public int $finish;
        }
    }
}

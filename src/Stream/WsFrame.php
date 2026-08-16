<?php

namespace Antonowano\Chat\Stream;

interface WsFrame
{
    public function data(): string;
}

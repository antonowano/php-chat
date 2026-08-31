<?php

namespace Antonowano\Chat\Stream\Controllers;

use Antonowano\Chat\AccessControl;
use Antonowano\Chat\Events;
use Antonowano\Chat\MessageStorage;
use Antonowano\Chat\NewMessage;
use Antonowano\Chat\RoomStorage;
use Antonowano\Chat\Stream\StreamFrame;
use Antonowano\Chat\Stream\StreamResponse;

readonly class MessageController
{
    public function __construct(
        private Events $events,
        private MessageStorage $messageStorage,
        private RoomStorage $roomStorage,
        private AccessControl $accessControl,
    ) {
    }

    public function send(StreamFrame $frame, StreamResponse $response): void
    {
        $data = $frame->data();
        $roomId = $data->get('roomId');
        $room = $this->roomStorage->findById($roomId);

        if (!$this->accessControl->isGranted($frame->user(), 'room.write', $room)) {
            $response->sendForbidden($frame->correlationId());
            return;
        }

        $message = $this->messageStorage->create(new NewMessage(
            roomId: $roomId,
            text: $data->get('text'),
            author: $frame->user(),
        ));
        $this->events->messageSent($message);
        $response->sendCreated($frame->correlationId());
    }

    public function last(StreamFrame $frame, StreamResponse $response): void
    {
        $data = $frame->data();
        $roomId = $data->get('roomId');
        $room = $this->roomStorage->findById($roomId);

        if (!$this->accessControl->isGranted($frame->user(), 'room.read', $room)) {
            $response->sendForbidden($frame->correlationId());
            return;
        }

        $messages = $this->messageStorage->getLastMessages($roomId, 30);
        $response->sendMessageList($frame->correlationId(), $messages);
    }

    public function next(StreamFrame $frame, StreamResponse $response): void
    {
        $data = $frame->data();
        $roomId = $data->get('roomId');
        $room = $this->roomStorage->findById($roomId);

        if (!$this->accessControl->isGranted($frame->user(), 'room.read', $room)) {
            $response->sendForbidden($frame->correlationId());
            return;
        }

        $messages = $this->messageStorage->getMessagesAfterId(
            $roomId,
            $data->get('id', 0),
            30
        );
        $response->sendMessageList($frame->correlationId(), $messages);
    }

    public function previous(StreamFrame $frame, StreamResponse $response): void
    {
        $data = $frame->data();
        $roomId = $data->get('roomId');
        $room = $this->roomStorage->findById($roomId);

        if (!$this->accessControl->isGranted($frame->user(), 'room.read', $room)) {
            $response->sendForbidden($frame->correlationId());
            return;
        }

        $messages = $this->messageStorage->getMessagesBeforeId(
            $roomId,
            $data->get('id', 0),
            30
        );
        $response->sendMessageList($frame->correlationId(), $messages);
    }
}

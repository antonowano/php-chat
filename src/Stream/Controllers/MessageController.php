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
            return;
        }

        $message = $this->messageStorage->create(new NewMessage(
            roomId: $roomId,
            text: $data->get('text'),
            author: $frame->user(),
        ));
        $this->events->messageSent($message);
    }

    public function last(StreamFrame $frame, StreamResponse $response): void
    {
        $roomId = $frame->data()->get('roomId', 0);
        $messages = $this->messageStorage->getLastMessages($roomId, 30);
        $response->sendMessageList('LastMessages', $messages);
    }

    public function next(StreamFrame $frame, StreamResponse $response): void
    {
        $data = $frame->data();
        $messages = $this->messageStorage->getMessagesAfterId(
            $data->get('roomId', 0),
            $data->get('id', 0),
            30
        );
        $response->sendMessageList('NextMessages', $messages);
    }

    public function previous(StreamFrame $frame, StreamResponse $response): void
    {
        $data = $frame->data();
        $messages = $this->messageStorage->getMessagesBeforeId(
            $data->get('roomId', 0),
            $data->get('id', 0),
            30
        );
        $response->sendMessageList('PreviousMessages', $messages);
    }
}

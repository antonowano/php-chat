<?php

namespace Tests\Antonowano\Chat\Unit;

class NewMessageTest extends TestCase
{
    public function testEquals(): void
    {
        $this->assertEquals(
            $this->createNewMessage(1, 'Hello, World!', 'Ivan'),
            $this->createNewMessage(1, 'Hello, World!', 'Ivan')
        );
    }

    public function testNotEquals(): void
    {
        $this->assertNotEquals(
            $this->createNewMessage(2, 'Hello, World!1', 'Ivan'),
            $this->createNewMessage(2, 'Hello, World!', 'Ivan')
        );
    }

    public function testNotEquals2(): void
    {
        $this->assertNotEquals(
            $this->createNewMessage(3, 'Hello, World!', 'Ivan'),
            $this->createNewMessage(3, 'Hello, World!', 'Olga')
        );
    }
}
<?php

namespace Tests\Antonowano\Chat\Unit;

class NewMessageTest extends TestCase
{
    public function testEquals(): void
    {
        $this->assertEquals(
            $this->createNewMessage('Hello, World!', 'Ivan'),
            $this->createNewMessage('Hello, World!', 'Ivan')
        );
    }

    public function testNotEquals(): void
    {
        $this->assertNotEquals(
            $this->createNewMessage('Hello, World!1', 'Ivan'),
            $this->createNewMessage('Hello, World!', 'Ivan')
        );
    }

    public function testNotEquals2(): void
    {
        $this->assertNotEquals(
            $this->createNewMessage('Hello, World!', 'Ivan'),
            $this->createNewMessage('Hello, World!', 'Olga')
        );
    }
}
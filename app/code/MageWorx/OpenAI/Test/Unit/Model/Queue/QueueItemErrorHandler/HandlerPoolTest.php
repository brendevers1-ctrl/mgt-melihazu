<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types = 1);

namespace MageWorx\OpenAI\Test\Unit\Model\Queue\QueueItemErrorHandler;

use MageWorx\OpenAI\Api\Data\QueueItemInterface;
use MageWorx\OpenAI\Api\QueueItemErrorHandlerInterface;
use MageWorx\OpenAI\Model\Queue\QueueItemErrorHandler\HandlerPool;
use PHPUnit\Framework\TestCase;

class HandlerPoolTest extends TestCase
{
    public function testGetByTypeReturnsEmptyArrayWhenNoHandlersForType()
    {
        $handlerPool = new HandlerPool();
        $this->assertEquals([], $handlerPool->getByType('nonexistent_type'));
    }

    public function testGetByTypeReturnsHandlersForType()
    {
        $handlers    = ['type1' => ['handler1', 'handler2']];
        $handlerPool = new HandlerPool($handlers);
        $this->assertEquals(['handler1', 'handler2'], $handlerPool->getByType('type1'));
    }

    public function testProcessReturnsFalseWhenNoHandlersForType()
    {
        $queueItem   = $this->createMock(QueueItemInterface::class);
        $handlerPool = new HandlerPool();
        $this->assertFalse($handlerPool->process('nonexistent_type', $queueItem));
    }

    public function testProcessReturnsTrueWhenHandlerExecutesSuccessfully()
    {
        $queueItem = $this->getMockForAbstractClass(QueueItemInterface::class);
        $handler   = $this->getMockForAbstractClass(QueueItemErrorHandlerInterface::class);
        $handler->method('execute')->willReturn(true);
        $handlers    = ['type1' => [$handler]];
        $handlerPool = new HandlerPool($handlers);
        $this->assertTrue($handlerPool->process('type1', $queueItem));
    }

    public function testProcessReturnsFalseWhenAllHandlersFail()
    {
        $queueItem = $this->getMockForAbstractClass(QueueItemInterface::class);
        $handler1  = $this->getMockForAbstractClass(QueueItemErrorHandlerInterface::class);
        $handler1->method('execute')->willReturn(false);
        $handler2 = $this->getMockForAbstractClass(QueueItemErrorHandlerInterface::class);
        $handler2->method('execute')->willReturn(false);
        $handlers    = ['type1' => [$handler1, $handler2]];
        $handlerPool = new HandlerPool($handlers);
        $this->assertFalse($handlerPool->process('type1', $queueItem));
    }
}

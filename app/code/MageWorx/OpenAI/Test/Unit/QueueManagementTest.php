<?php

namespace MageWorx\OpenAI\Test\Unit;

use MageWorx\OpenAI\Api\Data\QueueItemInterface;
use MageWorx\OpenAI\Api\OptionsInterface;
use MageWorx\OpenAI\Api\QueueProcessorInterface;
use MageWorx\OpenAI\Model\Queue\Callback\CallbackFactory;
use MageWorx\OpenAI\Model\Queue\QueueItem;
use MageWorx\OpenAI\Model\Queue\QueueManagement;
use MageWorx\OpenAI\Model\Queue\QueueProcess;
use MageWorx\OpenAI\Model\Queue\QueueRepository;
use MageWorx\OpenAI\Model\Queue\QueueItemFactory;
use MageWorx\OpenAI\Model\Queue\QueueProcessFactory;
use MageWorx\OpenAI\Model\ResourceModel\QueueItem as QueueItemResource;
use PHPUnit\Framework\TestCase;

class QueueManagementTest extends TestCase
{
    /**
     * @var (QueueItemFactory&\PHPUnit\Framework\MockObject\MockObject)|\PHPUnit\Framework\MockObject\MockObject
     */
    private $queueItemFactoryMock;
    /**
     * @var (QueueItem&\PHPUnit\Framework\MockObject\MockObject)|\PHPUnit\Framework\MockObject\MockObject
     */
    private $queueItemMock;
    /**
     * @var (QueueItemResource&\PHPUnit\Framework\MockObject\MockObject)|\PHPUnit\Framework\MockObject\MockObject
     */
    private $queueItemResourceMock;
    /**
     * @var (QueueRepository&\PHPUnit\Framework\MockObject\MockObject)|\PHPUnit\Framework\MockObject\MockObject
     */
    private $queueRepositoryMock;
    /**
     * @var (OptionsInterface&\PHPUnit\Framework\MockObject\MockObject)|\PHPUnit\Framework\MockObject\MockObject
     */
    private $optionsMock;
    /**
     * @var (QueueProcess&\PHPUnit\Framework\MockObject\MockObject)|\PHPUnit\Framework\MockObject\MockObject
     */
    private $queueProcessMock;
    /**
     * @var (QueueProcessorInterface&\PHPUnit\Framework\MockObject\MockObject)|\PHPUnit\Framework\MockObject\MockObject
     */
    private $queueProcessorMock;
    /**
     * @var (CallbackFactory&\PHPUnit\Framework\MockObject\MockObject)|\PHPUnit\Framework\MockObject\MockObject
     */
    private $callbackFactoryMock;

    public function setUp(): void
    {
        parent::setUp();

        $this->queueItemFactoryMock = $this->getMockBuilder(QueueItemFactory::class)
                                           ->disableOriginalConstructor()
                                           ->getMock();

        $this->queueItemMock = $this->getMockBuilder(QueueItem::class)
                                    ->disableOriginalConstructor()
                                    ->getMock();

        $this->queueItemResourceMock = $this->getMockBuilder(QueueItemResource::class)
                                            ->disableOriginalConstructor()
                                            ->getMock();

        $this->queueRepositoryMock = $this->getMockBuilder(QueueRepository::class)
                                          ->disableOriginalConstructor()
                                          ->getMock();

        $this->optionsMock = $this->getMockBuilder(OptionsInterface::class)
                                  ->disableOriginalConstructor()
                                  ->getMock();

        $this->queueProcessMock = $this->getMockBuilder(QueueProcess::class)
                                       ->disableOriginalConstructor()
                                       ->getMock();

        $this->queueProcessorMock = $this->getMockBuilder(QueueProcessorInterface::class)
                                         ->disableOriginalConstructor()
                                         ->getMockForAbstractClass();

        $this->callbackFactoryMock = $this->getMockBuilder(CallbackFactory::class)
                                          ->disableOriginalConstructor()
                                          ->getMock();

        $this->loggerMock = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)
                                 ->disableOriginalConstructor()
                                 ->getMockForAbstractClass();

        $this->queueManagement = new QueueManagement(
            $this->queueItemFactoryMock,
            $this->queueItemResourceMock,
            $this->queueRepositoryMock,
            $this->queueProcessorMock,
            $this->callbackFactoryMock,
            $this->loggerMock
        );
    }

    /**
     * Check is new queue item created successfully with all data required
     *
     * @covers \MageWorx\OpenAI\Model\Queue\QueueManagement::addToQueue
     */
    public function testMethodsCallOfAddToQueue(): void
    {
        // Declare input params
        $content       = 'test';
        $context       = [];
        $options       = $this->optionsMock;
        $callback      = 'MageWorx\OpenAI\Model\Queue\Callback\DummyCallback';
        $process       = $this->queueProcessMock;
        $requestDataId = 3;
        $processId     = 1;
        $model         = 'gpt-3.5-turbo-16k';

        $this->optionsMock->expects($this->once())
                          ->method('getModel')
                          ->willReturn($model);

        $this->queueItemFactoryMock->expects($this->once())
                                   ->method('create')
                                   ->willReturn($this->queueItemMock);

        $this->queueItemMock->expects($this->once())
                            ->method('setContent')
                            ->with($content)
                            ->willReturnSelf();

        $this->queueItemMock->expects($this->once())
                            ->method('setContext')
                            ->with($context)
                            ->willReturnSelf();

        $this->queueItemMock->expects($this->once())
                            ->method('setOptions')
                            ->with($options)
                            ->willReturnSelf();

        $this->queueItemMock->expects($this->once())
                            ->method('setCallback')
                            ->with($callback)
                            ->willReturnSelf();

        $this->queueItemMock->expects($this->once())
                            ->method('setModel')
                            ->with($model)
                            ->willReturnSelf();

        $this->queueItemMock->expects($this->once())
                            ->method('setRequestDataId')
                            ->with($requestDataId)
                            ->willReturnSelf();

        $this->queueItemMock->expects($this->once())
                            ->method('setStatus')
                            ->with(QueueItemInterface::STATUS_PENDING)
                            ->willReturnSelf();

        $this->queueItemResourceMock->expects($this->once())
                                    ->method('save')
                                    ->with($this->queueItemMock)
                                    ->willReturnSelf();

        $this->queueProcessMock->expects($this->once())
                               ->method('getId')
                               ->willReturn($processId);

        $this->queueItemMock->expects($this->once())
                            ->method('setProcessId')
                            ->with($processId)
                            ->willReturnSelf();

        $this->queueItemResourceMock->expects($this->once())
                                    ->method('saveRequestData')
                                    ->with($content, $context, $options)
                                    ->willReturn($requestDataId);

        $queueItem = $this->queueManagement->addToQueue($content, $options, $callback, $context, $process);

        $this->assertInstanceOf(QueueItemInterface::class, $queueItem);
    }
}

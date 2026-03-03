<?php

namespace MageWorx\OpenAI\Test\Unit;

use Magento\Framework\Serialize\Serializer\Json;
use MageWorx\OpenAI\Api\Data\QueueProcessInterface;
use MageWorx\OpenAI\Api\Data\QueueProcessInterfaceFactory as QueueProcessFactory;
use MageWorx\OpenAI\Model\Queue\QueueProcess;
use MageWorx\OpenAI\Model\Queue\QueueProcessManagement;
use MageWorx\OpenAI\Model\ResourceModel\QueueProcess as QueueProcessResource;
use MageWorx\OpenAI\Model\ResourceModel\QueueProcess\Collection as QueueProcessCollection;
use MageWorx\OpenAI\Model\ResourceModel\QueueProcess\CollectionFactory as QueueProcessCollectionFactory;
use PHPUnit\Framework\TestCase;

class QueueProcessManagementTest extends TestCase
{
    protected $queueProcessManagement;
    protected $queueProcessFactoryMock;
    protected $queueProcessResourceMock;
    protected $queueCollectionFactoryMock;
    protected $jsonSerializerMock;

    protected function setUp(): void
    {
        // Mock dependencies
        $this->queueProcessFactoryMock    = $this->createMock(QueueProcessFactory::class);
        $this->queueProcessResourceMock   = $this->createMock(QueueProcessResource::class);
        $this->queueCollectionFactoryMock = $this->createMock(QueueProcessCollectionFactory::class);
        $this->jsonSerializerMock         = $this->createMock(Json::class);

        // Instantiate the class to be tested
        $this->queueProcessManagement = new QueueProcessManagement(
            $this->queueProcessFactoryMock,
            $this->queueProcessResourceMock,
            $this->queueCollectionFactoryMock,
            $this->jsonSerializerMock
        );
    }

    /**
     * @covers QueueProcessManagement::getExistingProcessByName
     *
     * @return void
     */
    public function testGetExistingProcessByName(): void
    {
        $processName = 'testProcess';
        $processMock = $this->createMock(QueueProcess::class);

        // Set up the factory to return a new process mock
        $this->queueProcessFactoryMock->method('create')->willReturn($processMock);

        $collectionMock = $this->createMock(QueueProcessCollection::class);
        $this->queueCollectionFactoryMock->method('create')->willReturn($collectionMock);
        $collectionMock->expects($this->once())->method('addFieldToFilter')->with('name', $processName)->willReturnSelf();
        $collectionMock->expects($this->once())->method('setPageSize')->with(1)->willReturnSelf();
        $collectionMock->expects($this->once())->method('setCurPage')->with(1)->willReturnSelf();
        $collectionMock->method('getSize')->willReturn(1);
        $collectionMock->method('getFirstItem')->willReturn($processMock);

        // Execute the method
        $result = $this->queueProcessManagement->getExistingProcessByName($processName);

        // Assertions
        $this->assertInstanceOf(QueueProcessInterface::class, $result);
    }

    /**
     * @covers QueueProcessManagement::getExistingProcessByCode
     *
     * @return void
     */
    public function testGetExistingProcessByCode(): void
    {
        $processCode = 'testProcessCode';
        $processMock = $this->createMock(QueueProcess::class);

        // Set up the factory to return a new process mock
        $this->queueProcessFactoryMock->method('create')->willReturn($processMock);

        $collectionMock = $this->createMock(QueueProcessCollection::class);
        $this->queueCollectionFactoryMock->method('create')->willReturn($collectionMock);
        $collectionMock->expects($this->once())->method('addFieldToFilter')->with('code', $processCode)->willReturnSelf();
        $collectionMock->expects($this->once())->method('setPageSize')->with(1)->willReturnSelf();
        $collectionMock->expects($this->once())->method('setCurPage')->with(1)->willReturnSelf();
        $collectionMock->method('getSize')->willReturn(1);
        $collectionMock->method('getFirstItem')->willReturn($processMock);

        // Execute the method
        $result = $this->queueProcessManagement->getExistingProcessByCode($processCode);

        // Assertions
        $this->assertInstanceOf(QueueProcessInterface::class, $result);
    }

    /**
     * @covers QueueProcessManagement::registerProcess
     *
     * @return void
     */
    public function testRegisterProcess(): void
    {
        $processCode    = 'testProcessCode';
        $processType    = 'testProcessType';
        $processName    = 'testProcessName';
        $processModule  = 'testProcessModule';
        $processSize    = 10;
        $additionalData = ['key' => 'value'];
        $encodedData    = '{"key":"value"}';

        $processMock = $this->createMock(QueueProcess::class);
        $this->queueProcessFactoryMock->method('create')->willReturn($processMock);

        $collectionMock = $this->createMock(QueueProcessCollection::class);
        $this->queueCollectionFactoryMock->method('create')->willReturn($collectionMock);
        $collectionMock->expects($this->once())->method('addFieldToFilter')->with('code', $processCode)->willReturnSelf();
        $collectionMock->expects($this->once())->method('setPageSize')->with(1)->willReturnSelf();
        $collectionMock->expects($this->once())->method('setCurPage')->with(1)->willReturnSelf();
        $collectionMock->method('getSize')->willReturn(0);
        $collectionMock->method('getFirstItem')->willReturn($processMock);

        $this->jsonSerializerMock->expects($this->once())->method('serialize')->with($additionalData)->willReturn($encodedData);

        // No data in empty (new) process entity
        $processMock->expects($this->once())->method('getAdditionalData')->willReturn(null);
        // Empty size in empty (new) process entity
        $processMock->expects($this->once())->method('getSize')->willReturn(0);
        $processMock->expects($this->once())->method('setCode')->with($processCode)->willReturnSelf();
        $processMock->expects($this->once())->method('setType')->with($processType)->willReturnSelf();
        $processMock->expects($this->once())->method('setName')->with($processName)->willReturnSelf();
        $processMock->expects($this->once())->method('setModule')->with($processModule)->willReturnSelf();
        $processMock->expects($this->once())->method('setSize')->with($processSize)->willReturnSelf();
        $processMock->expects($this->once())->method('setAdditionalData')->with($encodedData)->willReturnSelf();
        // Do not update processed items when registering process
        $processMock->expects($this->never())->method('setProcessed');

        $this->queueProcessResourceMock->expects($this->once())->method('save')->with($processMock);

        // Execute the method
        $result = $this->queueProcessManagement->registerProcess($processCode, $processType, $processName, $processModule, $processSize, $additionalData);

        // Assertions
        $this->assertInstanceOf(QueueProcessInterface::class, $result);
    }

    /**
     * @covers QueueProcessManagement::setQueueItemProcessed
     *
     * @return void
     */
    public function testSetQueueItemProcessed(): void
    {
        $processId = 203;

        $processMock = $this->createMock(QueueProcess::class);
        $processMock->expects($this->once())->method('getProcessed')->willReturn(10);
        $processMock->expects($this->once())->method('setProcessed')->with(11)->willReturnSelf();
        $this->queueProcessFactoryMock->method('create')->willReturn($processMock);

        $this->queueProcessResourceMock->expects($this->atLeastOnce())->method('load')->with($processMock, $processId)
                                       ->willReturn($processMock);
        $this->queueProcessResourceMock->expects($this->once())->method('save')->with($processMock)
                                       ->willReturn($processMock);

        $this->queueProcessManagement->setQueueItemProcessed($processId);
    }

    /**
     * @covers QueueProcessManagement::setQueueItemProcessedByCode
     *
     * @return void
     */
    public function testSetQueueItemProcessedByCode(): void
    {
        $processCode = 'code203';

        $processMock = $this->createMock(QueueProcess::class);
        $processMock->expects($this->once())->method('getProcessed')->willReturn(10);
        $processMock->expects($this->once())->method('setProcessed')->with(11)->willReturnSelf();
        $this->queueProcessFactoryMock->method('create')->willReturn($processMock);

        $this->queueProcessResourceMock->expects($this->atLeastOnce())
                                       ->method('load')
                                       ->with($processMock, $processCode, 'code')
                                       ->willReturn($processMock);
        $this->queueProcessResourceMock->expects($this->once())
                                       ->method('save')
                                       ->with($processMock)
                                       ->willReturn($processMock);

        $this->queueProcessManagement->setQueueItemProcessedByCode($processCode);
    }
}

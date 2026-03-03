<?php

namespace MageWorx\OpenAI\Test\Unit\Model\ResourceModel;

use MageWorx\OpenAI\Model\ResourceModel\QueueItem;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Serialize\Serializer\Json;
use MageWorx\OpenAI\Api\OptionsInterface;
use PHPUnit\Framework\TestCase;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class QueueItemResourceModelTest extends TestCase
{
    private QueueItem $queueItemResourceModel;
    private Json      $jsonSerializerMock;
    private Mysql     $connectionMock;
    private Context   $contextMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        // Mock dependencies
        $this->contextMock = $this->getMockBuilder(Context::class)
                                  ->disableOriginalConstructor()
                                  ->setMethods(['getResources'])
                                  ->getMock();

        $this->jsonSerializerMock = $this->createMock(Json::class);
        $this->connectionMock     = $this->getMockBuilder(Mysql::class)
                                         ->disableOriginalConstructor()
                                         ->getMock();

        $this->resourceConnectionMock = $this->getMockBuilder(\Magento\Framework\App\ResourceConnection::class)
                                             ->disableOriginalConstructor()
                                             ->getMock();

        $this->resourceConnectionMock->method('getConnection')->willReturn($this->connectionMock);

        $this->contextMock->method('getResources')->willReturn($this->resourceConnectionMock);
        $this->resourceConnectionMock->method('getTableName')
                                     ->with('mageworx_openai_request_data')
                                     ->willReturn('mageworx_openai_request_data');

        // Instantiate the class to be tested
        $this->queueItemResourceModel = $objectManager->getObject(
            QueueItem::class,
            [
                'context'        => $this->contextMock,
                'jsonSerializer' => $this->jsonSerializerMock
            ]
        );
    }

    /**
     * @return void
     */
    public function testSaveRequestData(): void
    {
        $content           = 'testContent';
        $context           = ['key' => 'value'];
        $optionsMock       = $this->createMock(OptionsInterface::class);
        $serializedContext = '{"key":"value"}';
        $serializedOptions = '{"optionKey":"optionValue"}';

        $optionsMock->method('toArray')->willReturn(['optionKey' => 'optionValue']);
        $this->jsonSerializerMock->method('serialize')->willReturnOnConsecutiveCalls($serializedContext, $serializedOptions);

        $this->connectionMock->method('insert')->willReturnSelf();
        $this->connectionMock->method('lastInsertId')->willReturn('123');

        $result = $this->queueItemResourceModel->saveRequestData($content, $context, $optionsMock);

        // Assertions
        $this->assertEquals(123, $result);
    }
}

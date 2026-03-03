<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types = 1);

namespace MageWorx\OpenAI\Test\Integration;

use Magento\TestFramework\Helper\Bootstrap;
use MageWorx\OpenAI\Api\Data\QueueProcessInterface;
use MageWorx\OpenAI\Model\Queue\QueueProcessManagement;
use PHPUnit\Framework\TestCase;

/**
 * @magentoDbIsolation disabled
 */
class QueueProcessCreationTest extends TestCase
{
    private $queueProcessManagement;

    protected function setUp(): void
    {
        $this->queueProcessManagement = Bootstrap::getObjectManager()->get(QueueProcessManagement::class);
    }

    /**
     * Test process registration for dummy module.
     * Verifies correct registration, loading, data accuracy, and database storage.
     *
     * @return string
     */
    public function testRegisterProcess(): string
    {
        $code           = 'test_code';
        $type           = 'test_type';
        $name           = 'Test Process';
        $module         = 'Test_Module';
        $size           = 10;
        $additionalData = ['key' => 'value'];

        $process = $this->queueProcessManagement->registerProcess($code, $type, $name, $module, $size, $additionalData);

        $this->assertInstanceOf(QueueProcessInterface::class, $process);
        $this->assertEquals($code, $process->getCode());
        $this->assertEquals($type, $process->getType());
        $this->assertEquals($name, $process->getName());
        $this->assertEquals($module, $process->getModule());
        $this->assertEquals($size, $process->getSize());
        $this->assertEquals(json_encode($additionalData), $process->getAdditionalData());

        return $code;
    }

    /**
     * @depends testRegisterProcess
     */
    public function testLoadProcessByCode(string $code)
    {
        $process = $this->queueProcessManagement->getExistingProcessByCode($code);

        $this->assertInstanceOf(QueueProcessInterface::class, $process);
        $this->assertEquals($code, $process->getCode());
        $this->assertEquals('test_type', $process->getType());
        $this->assertEquals('Test Process', $process->getName());
        $this->assertEquals('Test_Module', $process->getModule());
        $this->assertEquals(10, $process->getSize());
        $this->assertEquals(json_encode(['key' => 'value']), $process->getAdditionalData());
    }
}

<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types = 1);

namespace Unit\Model\ResourceModel;

use Magento\Framework\DB\Adapter\Pdo\Mysql as AdapterPdoMysql;
use Magento\Framework\DB\Select;
use MageWorx\OpenAI\Model\ResourceModel\DependencyChecker;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 *  Tests the functionality of the DependencyChecker resource model.
 */
class DependencyCheckerTest extends TestCase
{
    /**
     * @var DependencyChecker|MockObject
     * A mock of the DependencyChecker resource model.
     */
    private $dependencyChecker;

    /**
     * @var AdapterPdoMysql|MockObject
     * A mock of the database adapter used for testing.
     */
    private $connectionMock;

    /**
     * @var MockObject
     * A mock of the database context used to instantiate the resource model.
     */
    private $contextMock;

    /**
     * @var MockObject
     * A mock of the ResourceConnection providing database connection.
     */
    private $resourceMock;

    /**
     * Setup for tests, creating mock objects for the resource model and database connection.
     */
    protected function setUp(): void
    {
        $this->contextMock    = $this->createMock(\Magento\Framework\Model\ResourceModel\Db\Context::class);
        $this->connectionMock = $this->createMock(AdapterPdoMysql::class);

        $this->resourceMock = $this->createMock(\Magento\Framework\App\ResourceConnection::class);
        $this->resourceMock->method('getConnection')->willReturn($this->connectionMock);

        $this->contextMock->method('getResources')->willReturn($this->resourceMock);

        $this->dependencyChecker = $this->getMockBuilder(DependencyChecker::class)
                                        ->setConstructorArgs([$this->contextMock])
                                        ->onlyMethods(['getConnection'])
                                        ->getMock();

        $this->dependencyChecker->method('getConnection')->willReturn($this->connectionMock);
    }

    /**
     * Tests whether dependencies are considered ready when all are actually ready.
     */
    public function testAreDependenciesReadyWhenAllReady(): void
    {
        $queueItemId = 123;

        // Create a mock for the Select object
        $selectMock = $this->createMock(Select::class);
        $selectMock->method('from')->willReturnSelf();
        $selectMock->method('join')->willReturnSelf();
        $selectMock->method('where')->willReturnSelf();

        // Configure the connection mock to return the Select mock when select() is called
        $this->connectionMock->method('select')->willReturn($selectMock);

        // Configure the connection mock to return an empty array when fetchCol() is called
        $this->connectionMock->method('fetchCol')->willReturn([]);

        $this->assertTrue($this->dependencyChecker->areDependenciesReady($queueItemId));
    }

    /**
     * Tests whether dependencies are considered not ready when at least one is not ready.
     */
    public function testAreDependenciesReadyWhenNotAllReady(): void
    {
        $queueItemId = 123;

        // Create a mock for the Select object
        $selectMock = $this->createMock(Select::class);
        $selectMock->method('from')->willReturnSelf();
        $selectMock->method('join')->willReturnSelf();
        $selectMock->method('where')->willReturnSelf();

        // Configure the connection mock to return the Select mock when select() is called
        $this->connectionMock->method('select')->willReturn($selectMock);

        // Configure the connection mock to return an array with an ID of a not ready dependency
        $this->connectionMock->method('fetchCol')->willReturn([456]); // Example of a not ready dependency ID

        $this->assertFalse($this->dependencyChecker->areDependenciesReady($queueItemId));
    }
}

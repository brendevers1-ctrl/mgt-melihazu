<?php

namespace MageWorx\SeoAI\Test\Unit\Model;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use MageWorx\SeoAI\Api\Data\ProductRequestResponseLogSearchResultsInterface as SearchResultsInterface;
use MageWorx\SeoAI\Api\Data\ProductRequestResponseLogSearchResultsInterfaceFactory as SearchResultsFactory;
use MageWorx\SeoAI\Model\ProductRequestResponseLog;
use MageWorx\SeoAI\Model\ProductRequestResponseLogFactory;
use MageWorx\SeoAI\Model\ProductRequestResponseLogRepository;
use MageWorx\SeoAI\Model\ResourceModel\ProductRequestResponseLog as ProductRequestResponseLogResource;
use MageWorx\SeoAI\Model\ResourceModel\ProductRequestResponseLog\CollectionFactory as ProductRequestResponseLogCollectionFactory;
use PHPUnit\Framework\TestCase;

class ProductRequestResponseLogRepositoryTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var ProductRequestResponseLogRepository|object
     */
    protected $repository;

    /**
     * @var (ProductRequestResponseLogResource&\PHPUnit\Framework\MockObject\MockObject)|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resourceMock;

    /**
     * @var (ProductRequestResponseLogFactory&\PHPUnit\Framework\MockObject\MockObject)|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $logFactoryMock;

    /**
     * @var (ProductRequestResponseLogCollectionFactory&\PHPUnit\Framework\MockObject\MockObject)|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $logCollectionFactoryMock;

    /**
     * @var (CollectionProcessorInterface&\PHPUnit\Framework\MockObject\MockObject)|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $collectionProcessorMock;

    /**
     * @var (SearchResultsFactory&\PHPUnit\Framework\MockObject\MockObject)|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $searchResultsFactoryMock;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->resourceMock = $this->getMockBuilder(ProductRequestResponseLogResource::class)
                                   ->disableOriginalConstructor()
                                   ->getMock();

        $this->logFactoryMock = $this->getMockBuilder(ProductRequestResponseLogFactory::class)
                                     ->disableOriginalConstructor()
                                     ->setMethods(['create'])
                                     ->getMock();

        $this->logCollectionFactoryMock = $this->getMockBuilder(ProductRequestResponseLogCollectionFactory::class)
                                               ->disableOriginalConstructor()
                                               ->setMethods(['create'])
                                               ->getMock();

        $this->collectionProcessorMock = $this->getMockBuilder(CollectionProcessorInterface::class)
                                              ->getMockForAbstractClass();

        $this->searchResultsFactoryMock = $this->getMockBuilder(SearchResultsFactory::class)
                                               ->disableOriginalConstructor()
                                               ->setMethods(['create'])
                                               ->getMockForAbstractClass();

        $this->repository = $this->objectManager->getObject(
            ProductRequestResponseLogRepository::class,
            [
                'resource'             => $this->resourceMock,
                'logFactory'           => $this->logFactoryMock,
                'logCollectionFactory' => $this->logCollectionFactoryMock,
                'collectionProcessor'  => $this->collectionProcessorMock,
                'searchResultsFactory' => $this->searchResultsFactoryMock,
            ]
        );
    }

    /**
     * @return void
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function testSave()
    {
        $logMock = $this->getMockBuilder(ProductRequestResponseLog::class)
                        ->disableOriginalConstructor()
                        ->getMock();

        $this->resourceMock->expects($this->once())
                           ->method('save')
                           ->with($logMock)
                           ->willReturn($logMock);

        $this->assertEquals($logMock, $this->repository->save($logMock));
    }

    /**
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testGetById()
    {
        $logId   = 123;
        $logMock = $this->getMockBuilder(ProductRequestResponseLog::class)
                        ->disableOriginalConstructor()
                        ->getMock();

        $this->logFactoryMock->expects($this->once())
                             ->method('create')
                             ->willReturn($logMock);

        $this->resourceMock->expects($this->once())
                           ->method('load')
                           ->with($logMock, $logId);

        $logMock->expects($this->once())
                ->method('getId')
                ->willReturn($logId);

        $this->assertEquals($logMock, $this->repository->getById($logId));
    }

    /**
     * @return void
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function testDelete()
    {
        $logMock = $this->getMockBuilder(ProductRequestResponseLog::class)
                        ->disableOriginalConstructor()
                        ->getMock();

        $this->resourceMock->expects($this->once())
                           ->method('delete')
                           ->with($logMock);

        $this->assertTrue($this->repository->delete($logMock));
    }

    /**
     * @return void
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testDeleteById()
    {
        $logId = 123;

        $logMock = $this->getMockBuilder(ProductRequestResponseLog::class)
                        ->disableOriginalConstructor()
                        ->getMock();

        $repositoryMock = $this->getMockBuilder(ProductRequestResponseLogRepository::class)
                               ->disableOriginalConstructor()
                               ->setMethods(['getById', 'delete'])
                               ->getMock();

        $repositoryMock->expects($this->once())
                       ->method('getById')
                       ->with($logId)
                       ->willReturn($logMock);

        $repositoryMock->expects($this->once())
                       ->method('delete')
                       ->with($logMock)
                       ->willReturn(true);

        $this->assertTrue($repositoryMock->deleteById($logId));
    }

    /**
     * @return void
     */
    public function testGetList()
    {
        $searchCriteriaMock = $this->getMockBuilder(SearchCriteriaInterface::class)
                                   ->getMockForAbstractClass();
        $collectionMock     = $this->getMockBuilder(AbstractDb::class)
                                   ->disableOriginalConstructor()
                                   ->setMethods(['getSelect', 'getItems', 'getSize'])
                                   ->getMockForAbstractClass();
        $searchResultsMock  = $this->getMockBuilder(SearchResultsInterface::class)
                                   ->getMockForAbstractClass();

        $adapterMock = $this->getMockBuilder(\Magento\Framework\DB\Adapter\Pdo\Mysql::class)
                            ->disableOriginalConstructor()
                            ->getMock();

        $adapterMock->method('fetchAll')
                    ->willReturn([]);

        $selectMock = $this->getMockBuilder(\Magento\Framework\DB\Select::class)
                           ->disableOriginalConstructor()
                           ->getMock();

        $selectMock->method('getAdapter')
                   ->willReturn($adapterMock);

        $collectionMock->method('getSelect')
                       ->willReturn($selectMock);

        $collectionMock->expects($this->atLeastOnce())
                       ->method('getItems')
                       ->willReturn([]);

        $collectionMock->expects($this->atLeastOnce())
                       ->method('getSize')
                       ->willReturn(0);

        $this->logCollectionFactoryMock->expects($this->once())
                                       ->method('create')
                                       ->willReturn($collectionMock);

        $this->collectionProcessorMock->expects($this->once())
                                      ->method('process')
                                      ->with($searchCriteriaMock, $collectionMock);

        $this->searchResultsFactoryMock->expects($this->once())
                                       ->method('create')
                                       ->willReturn($searchResultsMock);

        $searchResultsMock->expects($this->once())
                          ->method('setSearchCriteria')
                          ->with($searchCriteriaMock);

        $this->assertEquals($searchResultsMock, $this->repository->getList($searchCriteriaMock));
    }
}

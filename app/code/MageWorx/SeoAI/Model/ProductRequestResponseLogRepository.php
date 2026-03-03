<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types = 1);

namespace MageWorx\SeoAI\Model;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use MageWorx\SeoAI\Api\Data\ProductRequestResponseLogInterface;
use MageWorx\SeoAI\Api\Data\ProductRequestResponseLogSearchResultsInterface as SearchResultsInterface;
use MageWorx\SeoAI\Api\Data\ProductRequestResponseLogSearchResultsInterfaceFactory as SearchResultsFactory;
use MageWorx\SeoAI\Api\ProductRequestResponseLogRepositoryInterface;
use MageWorx\SeoAI\Model\ResourceModel\ProductRequestResponseLog as ProductRequestResponseLogResource;
use MageWorx\SeoAI\Model\ResourceModel\ProductRequestResponseLog\CollectionFactory as ProductRequestResponseLogCollectionFactory;

class ProductRequestResponseLogRepository implements ProductRequestResponseLogRepositoryInterface
{
    /**
     * @var ProductRequestResponseLogResource
     */
    protected ProductRequestResponseLogResource $resource;

    /**
     * @var ProductRequestResponseLogFactory
     */
    protected ProductRequestResponseLogFactory $logFactory;

    /**
     * @var ProductRequestResponseLogCollectionFactory
     */
    protected ProductRequestResponseLogCollectionFactory $logCollectionFactory;

    /**
     * @var CollectionProcessorInterface
     */
    protected CollectionProcessorInterface $collectionProcessor;

    /**
     * @var SearchResultsFactory
     */
    protected SearchResultsFactory $searchResultsFactory;

    /**
     * @param ProductRequestResponseLogResource $resource
     * @param ProductRequestResponseLogFactory $logFactory
     * @param ProductRequestResponseLogCollectionFactory $logCollectionFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param SearchResultsFactory $searchResultsFactory
     */
    public function __construct(
        ProductRequestResponseLogResource          $resource,
        ProductRequestResponseLogFactory           $logFactory,
        ProductRequestResponseLogCollectionFactory $logCollectionFactory,
        CollectionProcessorInterface               $collectionProcessor,
        SearchResultsFactory                       $searchResultsFactory
    ) {
        $this->resource             = $resource;
        $this->logFactory           = $logFactory;
        $this->logCollectionFactory = $logCollectionFactory;
        $this->collectionProcessor  = $collectionProcessor;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    /**
     * @param ProductRequestResponseLogInterface $log
     * @return ProductRequestResponseLogInterface
     * @throws CouldNotSaveException
     */
    public function save(ProductRequestResponseLogInterface $log): ProductRequestResponseLogInterface
    {
        try {
            $this->resource->save($log);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(
                __(
                    'Could not save the log: %1',
                    $exception->getMessage()
                )
            );
        }
        return $log;
    }

    /**
     * @param int $logId
     * @return ProductRequestResponseLogInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $logId): ProductRequestResponseLogInterface
    {
        $log = $this->logFactory->create();
        $this->resource->load($log, $logId);
        if (!$log->getId()) {
            throw new NoSuchEntityException(__('The log with ID "%1" does not exist.', $logId));
        }
        return $log;
    }

    /**
     * @param ProductRequestResponseLogInterface $log
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(ProductRequestResponseLogInterface $log): bool
    {
        try {
            $this->resource->delete($log);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(
                __(
                    'Could not delete the log: %1',
                    $exception->getMessage()
                )
            );
        }
        return true;
    }

    /**
     * @param int $logId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById(int $logId): bool
    {
        return $this->delete($this->getById($logId));
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): SearchResultsInterface
    {
        $collection = $this->logCollectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());

        return $searchResults;
    }
}

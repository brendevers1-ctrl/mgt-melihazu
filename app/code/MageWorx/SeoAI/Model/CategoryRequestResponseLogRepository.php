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
use MageWorx\SeoAI\Api\CategoryRequestResponseLogRepositoryInterface;
use MageWorx\SeoAI\Api\Data\CategoryRequestResponseLogInterface;
use MageWorx\SeoAI\Api\Data\CategoryRequestResponseLogSearchResultsInterface as SearchResultsInterface;
use MageWorx\SeoAI\Api\Data\CategoryRequestResponseLogSearchResultsInterfaceFactory as SearchResultsFactory;
use MageWorx\SeoAI\Model\ResourceModel\CategoryRequestResponseLog as CategoryRequestResponseLogResource;
use MageWorx\SeoAI\Model\ResourceModel\CategoryRequestResponseLog\CollectionFactory as CategoryRequestResponseLogCollectionFactory;

class CategoryRequestResponseLogRepository implements CategoryRequestResponseLogRepositoryInterface
{
    /**
     * @var CategoryRequestResponseLogResource
     */
    protected CategoryRequestResponseLogResource $resource;

    /**
     * @var CategoryRequestResponseLogFactory
     */
    protected CategoryRequestResponseLogFactory $logFactory;

    /**
     * @var CategoryRequestResponseLogCollectionFactory
     */
    protected CategoryRequestResponseLogCollectionFactory $logCollectionFactory;

    /**
     * @var CollectionProcessorInterface
     */
    protected CollectionProcessorInterface $collectionProcessor;

    /**
     * @var SearchResultsFactory
     */
    protected SearchResultsFactory $searchResultsFactory;

    /**
     * @param CategoryRequestResponseLogResource $resource
     * @param CategoryRequestResponseLogFactory $logFactory
     * @param CategoryRequestResponseLogCollectionFactory $logCollectionFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param SearchResultsFactory $searchResultsFactory
     */
    public function __construct(
        CategoryRequestResponseLogResource          $resource,
        CategoryRequestResponseLogFactory           $logFactory,
        CategoryRequestResponseLogCollectionFactory $logCollectionFactory,
        CollectionProcessorInterface                $collectionProcessor,
        SearchResultsFactory                        $searchResultsFactory
    ) {
        $this->resource             = $resource;
        $this->logFactory           = $logFactory;
        $this->logCollectionFactory = $logCollectionFactory;
        $this->collectionProcessor  = $collectionProcessor;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    /**
     * @param CategoryRequestResponseLogInterface $log
     * @return CategoryRequestResponseLogInterface
     * @throws CouldNotSaveException
     */
    public function save(CategoryRequestResponseLogInterface $log): CategoryRequestResponseLogInterface
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
     * @return CategoryRequestResponseLogInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $logId): CategoryRequestResponseLogInterface
    {
        $log = $this->logFactory->create();
        $this->resource->load($log, $logId);
        if (!$log->getId()) {
            throw new NoSuchEntityException(__('The log with ID "%1" does not exist.', $logId));
        }
        return $log;
    }

    /**
     * @param CategoryRequestResponseLogInterface $log
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(CategoryRequestResponseLogInterface $log): bool
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

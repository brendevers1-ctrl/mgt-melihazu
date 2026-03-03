<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types = 1);

namespace MageWorx\OpenAI\Model\Queue;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsFactory;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use MageWorx\OpenAI\Api\Data\QueueProcessInterface;
use MageWorx\OpenAI\Api\Data\QueueProcessInterfaceFactory;
use MageWorx\OpenAI\Api\QueueProcessRepositoryInterface;
use MageWorx\OpenAI\Model\ResourceModel\QueueProcess as QueueProcessResource;
use MageWorx\OpenAI\Model\ResourceModel\QueueProcess\CollectionFactory as QueueProcessCollectionFactory;

class ProcessRepository implements QueueProcessRepositoryInterface
{
    protected QueueProcessInterfaceFactory  $queueItemFactory;
    protected QueueProcessResource          $queueProcessResource;
    protected QueueProcessCollectionFactory $queueProcessCollectionFactory;
    protected CollectionProcessorInterface  $collectionProcessor;
    protected SearchResultsFactory          $searchResultsFactory;

    public function __construct(
        QueueProcessInterfaceFactory  $queueItemFactory,
        QueueProcessResource          $queueProcessResource,
        QueueProcessCollectionFactory $queueProcessCollectionFactory,
        CollectionProcessorInterface  $collectionProcessor,
        SearchResultsFactory          $searchResultsFactory
    ) {
        $this->queueItemFactory              = $queueItemFactory;
        $this->queueProcessResource          = $queueProcessResource;
        $this->queueProcessCollectionFactory = $queueProcessCollectionFactory;
        $this->collectionProcessor           = $collectionProcessor;
        $this->searchResultsFactory          = $searchResultsFactory;
    }

    /**
     * @param QueueProcessInterface $item
     * @return QueueProcessInterface
     * @throws CouldNotSaveException
     */
    public function save(QueueProcessInterface $item): QueueProcessInterface
    {
        try {
            $this->queueProcessResource->save($item);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()), $exception);
        }

        return $item;
    }

    /**
     * @param int $id
     * @return QueueProcessInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $id): QueueProcessInterface
    {
        $item = $this->queueItemFactory->create();
        $this->queueProcessResource->load($item, $id);
        if (!$item->getId()) {
            throw new NoSuchEntityException(__('The queue item with the "%1" ID doesn\'t exist.', $id));
        }
        return $item;
    }

    /**
     * @param SearchCriteriaInterface $criteria
     * @return \Magento\Framework\Api\SearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $criteria)
    {
        $collection = $this->queueProcessCollectionFactory->create();
        $this->collectionProcessor->process($criteria, $collection);
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);

        $items = $collection->getItems();
        foreach ($items as $item) {
            $item->getResource()->afterLoad($item);
        }

        $searchResults->setItems($items);
        $searchResults->setTotalCount($collection->getSize());

        return $searchResults;
    }

    /**
     * @param QueueProcessInterface $item
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(QueueProcessInterface $item): bool
    {
        try {
            $this->queueProcessResource->delete($item);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()), $exception);
        }

        return true;
    }
}

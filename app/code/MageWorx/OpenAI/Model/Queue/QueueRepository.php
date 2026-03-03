<?php

declare(strict_types = 1);

namespace MageWorx\OpenAI\Model\Queue;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsFactory;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use MageWorx\OpenAI\Api\Data\QueueItemInterface;
use MageWorx\OpenAI\Api\Data\QueueItemInterfaceFactory;
use MageWorx\OpenAI\Api\QueueRepositoryInterface;
use MageWorx\OpenAI\Model\ResourceModel\QueueItem as QueueItemResource;
use MageWorx\OpenAI\Model\ResourceModel\QueueItem\CollectionFactory as QueueItemCollectionFactory;

class QueueRepository implements QueueRepositoryInterface
{
    protected QueueItemInterfaceFactory    $queueItemFactory;
    protected QueueItemResource            $queueItemResource;
    protected QueueItemCollectionFactory   $queueItemCollectionFactory;
    protected CollectionProcessorInterface $collectionProcessor;
    protected SearchResultsFactory         $searchResultsFactory;

    public function __construct(
        QueueItemInterfaceFactory    $queueItemFactory,
        QueueItemResource            $queueItemResource,
        QueueItemCollectionFactory   $queueItemCollectionFactory,
        CollectionProcessorInterface $collectionProcessor,
        SearchResultsFactory         $searchResultsFactory
    ) {
        $this->queueItemFactory           = $queueItemFactory;
        $this->queueItemResource          = $queueItemResource;
        $this->queueItemCollectionFactory = $queueItemCollectionFactory;
        $this->collectionProcessor        = $collectionProcessor;
        $this->searchResultsFactory       = $searchResultsFactory;
    }

    public function save(QueueItemInterface $item): QueueItemInterface
    {
        try {
            $this->queueItemResource->save($item);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()), $exception);
        }

        return $item;
    }

    public function getById(int $id): QueueItemInterface
    {
        $item = $this->queueItemFactory->create();
        $this->queueItemResource->load($item, $id);
        if (!$item->getId()) {
            throw new NoSuchEntityException(__('The queue item with the "%1" ID doesn\'t exist.', $id));
        }
        return $item;
    }

    public function getList(SearchCriteriaInterface $criteria)
    {
        $collection = $this->queueItemCollectionFactory->create();
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

    public function delete(QueueItemInterface $item): bool
    {
        try {
            $this->queueItemResource->delete($item);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()), $exception);
        }

        return true;
    }

    public function clearProcessed(): void
    {
        // Add logic to clear processed items. This might involve running a delete operation on the database with specific criteria.
    }
}

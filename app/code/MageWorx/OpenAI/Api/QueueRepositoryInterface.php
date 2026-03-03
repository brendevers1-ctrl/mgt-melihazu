<?php

declare(strict_types=1);

namespace MageWorx\OpenAI\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use MageWorx\OpenAI\Api\Data\QueueItemInterface;

/**
 * Interface for queue repository.
 */
interface QueueRepositoryInterface
{
    /**
     * Save queue item.
     *
     * @param QueueItemInterface $item
     * @return QueueItemInterface
     * @throws CouldNotSaveException
     */
    public function save(QueueItemInterface $item): QueueItemInterface;

    /**
     * Get queue item by ID.
     *
     * @param int $id
     * @return QueueItemInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $id): QueueItemInterface;

    /**
     * Get list of queue items based on search criteria.
     *
     * @param SearchCriteriaInterface $criteria
     * @return \Magento\Framework\Api\SearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $criteria);

    /**
     * Delete queue item.
     *
     * @param QueueItemInterface $item
     * @return bool True on successful deletion
     * @throws CouldNotDeleteException
     */
    public function delete(QueueItemInterface $item): bool;

    /**
     * Clear processed items from the queue.
     *
     * @return void
     */
    public function clearProcessed(): void;
}

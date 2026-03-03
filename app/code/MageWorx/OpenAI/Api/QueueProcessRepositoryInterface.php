<?php

declare(strict_types=1);

namespace MageWorx\OpenAI\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use MageWorx\OpenAI\Api\Data\QueueProcessInterface;

/**
 * Interface for queue repository.
 */
interface QueueProcessRepositoryInterface
{
    /**
     * Save queue item.
     *
     * @param QueueProcessInterface $item
     * @return QueueProcessInterface
     * @throws CouldNotSaveException
     */
    public function save(QueueProcessInterface $item): QueueProcessInterface;

    /**
     * Get queue item by ID.
     *
     * @param int $id
     * @return QueueProcessInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $id): QueueProcessInterface;

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
     * @param QueueProcessInterface $item
     * @return bool True on successful deletion
     * @throws CouldNotDeleteException
     */
    public function delete(QueueProcessInterface $item): bool;
}

<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types = 1);

namespace MageWorx\OpenAI\Controller\Adminhtml\QueueItem;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\MassAction\Filter;
use MageWorx\OpenAI\Api\Data\QueueItemInterface;
use MageWorx\OpenAI\Api\QueueManagementInterface;
use MageWorx\OpenAI\Model\ResourceModel\QueueItem\CollectionFactory as CollectionFactory;

abstract class AbstractQueueItemMassAction extends Action
{
    protected Filter                   $filter;
    protected CollectionFactory        $collectionFactory;
    protected QueueManagementInterface $queueManagement;

    public function __construct(
        Context                  $context,
        Filter                   $filter,
        CollectionFactory        $collectionFactory,
        QueueManagementInterface $queueManagement
    ) {
        parent::__construct($context);
        $this->filter            = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->queueManagement   = $queueManagement;
    }

    /**
     * Get the allowed statuses for callback.
     *
     * @return array The allowed statuses for callback.
     */
    public function getAllowedStatusesForCallback(): array
    {
        return [
            QueueItemInterface::STATUS_READY,
            QueueItemInterface::STATUS_COMPLETED,
            QueueItemInterface::STATUS_CALLBACK_FAILED,
            QueueItemInterface::STATUS_PENDING_REVIEW,
            QueueItemInterface::STATUS_DENIED
        ];
    }

    /**
     * Retrieves the process ID from any available source.
     *
     * This method attempts to get the process ID from the request parameters. If the 'process_id' parameter is present,
     * it is returned as an integer.
     *
     * If the 'process_id' parameter is not present, the method retrieves the first item from the collection returned
     * by the filter, and retrieves its process ID.
     *
     * If no process ID is found, null is returned.
     *
     * @return int|null The process ID if found, null otherwise.
     * @throws LocalizedException If an error occurs while retrieving the process ID.
     */
    protected function detectProcessId(): ?int
    {
        if ($this->getRequest()->getParam('process_id')) {
            return (int)$this->getRequest()->getParam('process_id');
        }

        $collection     = $this->filter->getCollection($this->collectionFactory->create());
        $firstQueueItem = $collection->getFirstItem();
        $processId      = (int)$firstQueueItem->getProcessId();

        return $processId;
    }
}

<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types = 1);

namespace MageWorx\OpenAI\Controller\Adminhtml\QueueItem;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\MassAction\Filter;
use MageWorx\OpenAI\Api\Data\QueueItemInterface;
use MageWorx\OpenAI\Api\QueueManagementInterface;
use MageWorx\OpenAI\Api\QueueRepositoryInterface as QueueItemRepository;
use MageWorx\OpenAI\Exception\CallbackProcessingException;
use MageWorx\OpenAI\Model\ResourceModel\QueueItem as QueueItemResource;
use MageWorx\OpenAI\Model\ResourceModel\QueueItem\Collection as Collection;
use MageWorx\OpenAI\Model\ResourceModel\QueueItem\CollectionFactory as CollectionFactory;

class MassRunQueueItemsCallback extends AbstractQueueItemMassAction
{
    const ADMIN_RESOURCE = 'MageWorx_OpenAI::manage_process';

    protected QueueItemRepository $queueItemRepository;

    public function __construct(
        Context                  $context,
        Filter                   $filter,
        CollectionFactory        $collectionFactory,
        QueueManagementInterface $queueManagement,
        QueueItemRepository      $queueItemRepository
    ) {
        parent::__construct($context, $filter, $collectionFactory, $queueManagement);
        $this->queueItemRepository = $queueItemRepository;
    }

    /**
     * @return Redirect
     * @throws LocalizedException
     */
    public function execute()
    {
        $successItems = 0;
        /** @var Collection $collection */
        $collection = $this->filter->getCollection(
            $this->collectionFactory->create()
        );

        /** @var QueueItemInterface[] $readyItems */
        $readyItems = $collection->addReadyDependenciesFilter()
                                 ->addFieldToFilter('status', ['in' => $this->getAllowedStatusesForCallback()])
                                 ->addFieldToFilter('callback', ['neq' => null])
                                 ->addOrder('position', 'ASC')
                                 ->getItems();

        /** @var QueueItemResource $queueItemResource */
        $queueItemResource = $collection->getResource();

        foreach ($readyItems as $item) {
            try {
                $item = $this->queueItemRepository->getById($item->getId());
                $this->queueManagement->processItemCallback($item);
                // Set queue item as completed
                $queueItemResource->updateStatus($item->getId(), QueueItemInterface::STATUS_COMPLETED);
                $successItems++;
            } catch (CallbackProcessingException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $queueItemResource->updateStatus($item->getId(), QueueItemInterface::STATUS_CALLBACK_FAILED);
            }
        }

        $this->messageManager
            ->addSuccessMessage(
                __('A total of %1 out of %2 records were applied.', $successItems, count($readyItems))
            );

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory
            ->create(ResultFactory::TYPE_REDIRECT);

        return $resultRedirect->setPath('*/*/');
    }
}

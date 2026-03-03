<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types = 1);

namespace MageWorx\OpenAI\Model\Queue;

use Magento\Framework\Exception\AlreadyExistsException;
use MageWorx\OpenAI\Api\CallbackInterface;
use MageWorx\OpenAI\Api\Data\QueueItemInterface;
use MageWorx\OpenAI\Api\Data\QueueProcessInterface;
use MageWorx\OpenAI\Api\OptionsInterface;
use MageWorx\OpenAI\Api\QueueManagementInterface;
use MageWorx\OpenAI\Api\QueueProcessorInterface;
use MageWorx\OpenAI\Api\QueueRepositoryInterface as QueueRepository;
use MageWorx\OpenAI\Exception\CallbackProcessingException;
use MageWorx\OpenAI\Exception\QueueEmptyException;
use MageWorx\OpenAI\Model\Queue\Callback\CallbackFactory;
use MageWorx\OpenAI\Model\ResourceModel\QueueItem as QueueItemResource;
use Psr\Log\LoggerInterface;

class QueueManagement implements QueueManagementInterface
{
    protected QueueItemFactory        $queueItemFactory;
    protected QueueItemResource       $queueItemResource;
    protected QueueRepository         $queueRepository;
    protected QueueProcessorInterface $queueProcessor;
    protected CallbackFactory         $callbackFactory;
    protected LoggerInterface         $logger;

    public function __construct(
        QueueItemFactory        $queueItemFactory,
        QueueItemResource       $queueItemResource,
        QueueRepository         $queueRepository,
        QueueProcessorInterface $queueProcessor,
        CallbackFactory         $callbackFactory,
        LoggerInterface         $logger
    ) {
        $this->queueItemFactory  = $queueItemFactory;
        $this->queueItemResource = $queueItemResource;
        $this->queueRepository   = $queueRepository;
        $this->queueProcessor    = $queueProcessor;
        $this->callbackFactory   = $callbackFactory;
        $this->logger            = $logger;
    }

    /**
     * Add a new item to the queue
     *
     * @param string $content
     * @param OptionsInterface $options
     * @param string|null $callbackModel
     * @param array|null $context
     * @param QueueProcessInterface|null $process
     * @param array|null $additionalData
     * @param string|null $preprocessor
     * @param string|null $errorHandler
     * @return QueueItemInterface
     * @throws AlreadyExistsException
     */
    public function addToQueue(
        string                 $content,
        OptionsInterface       $options,
        ?string                $callbackModel,
        ?array                 $context = [],
        ?QueueProcessInterface $process = null,
        ?array                 $additionalData = [],
        ?string                $preprocessor = null,
        ?string                $errorHandler = null,
        ?bool                  $requiresApproval = false
    ): QueueItemInterface {
        // Saving request data in storage
        $requestDataId = $this->queueItemResource->saveRequestData($content, $context, $options);

        // Creating queue item
        /** @var QueueItemInterface $queueItem */
        $queueItem = $this->queueItemFactory->create();
        $queueItem->setContent($content)
                  ->setContext($context)
                  ->setOptions($options)
                  ->setModel($options->getModel())
                  ->setCallback($callbackModel)
                  ->setStatus(QueueItemInterface::STATUS_PENDING)
                  ->setRequestDataId($requestDataId)
                  ->setAdditionalData($additionalData)
                  ->setPreprocessor($preprocessor)
                  ->setErrorHandler($errorHandler)
                  ->setRequiresApproval($requiresApproval);

        if ($process) {
            $queueItem->setProcessId((int)$process->getId());
        }

        $this->queueItemResource->save($queueItem);

        return $queueItem;
    }

    /**
     * Logic to find the next pending item and process it
     * For example, sending a request to OpenAI and updating the item with the response
     * @return void
     */
    public function processNextItem(): void
    {
        $pendingItems = $this->getPendingItems();
        if (empty($pendingItems)) {
            throw new QueueEmptyException(__('No pending items found in the queue.'));
        }

        $item = array_shift($pendingItems);
        // Reload the item to make sure we have the latest data
        $item = $this->queueRepository->getById($item->getId());
        $this->queueItemResource->updateStatus($item->getId(), QueueItemInterface::STATUS_PROCESSING);

        try {
            $this->queueProcessor->processItem($item);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $this->queueItemResource->updateStatus($item->getId(), QueueItemInterface::STATUS_FAILED);
        }
    }

    /**
     * Logic to process all pending items in the queue
     *
     * @return void
     */
    public function processAll(): void
    {
        $pendingItems = $this->getPendingItems();
        foreach ($pendingItems as $item) {
            $this->processNextItem();
        }
    }

    /**
     * Logic to process all completed items in the queue
     *
     * @return void
     */
    public function processReadyItems(): void
    {
        $readyItems = $this->getReadyItems();
        foreach ($readyItems as $item) {
            try {
                $item = $this->queueRepository->getById($item->getId());
                $this->processItemCallback($item);
                // Set queue item as completed
                $this->queueItemResource->updateStatus($item->getId(), QueueItemInterface::STATUS_COMPLETED);
            } catch (CallbackProcessingException $e) {
                $this->logger->error($e->getMessage());
                $this->queueItemResource->updateStatus($item->getId(), QueueItemInterface::STATUS_CALLBACK_FAILED);
            }
        }
    }

    /**
     * Process the callback for a queue item.
     *
     * @param QueueItemInterface $item
     * @return void
     */
    public function processItemCallback(QueueItemInterface $item): void
    {
        $callbackModelClass = $item->getCallback();
        /** @var CallbackInterface $callbackInstance */
        $callbackInstance = $this->callbackFactory->create($callbackModelClass);

        $callbackInstance->execute($item->getOptions(), $item->getResponse(), $item->getAdditionalData());
    }

    /**
     * Logic to retrieve all pending items from the queue
     * This could involve fetching items with a 'pending' status from your database or queue system
     *
     * @return array|QueueItemInterface[]
     */
    public function getPendingItems(): array
    {
        $processableStatuses = [
            QueueItemInterface::STATUS_PENDING,
            QueueItemInterface::STATUS_FAILED
        ];

        /** @var \MageWorx\OpenAI\Model\ResourceModel\QueueItem\Collection $collection */
        $collection   = $this->queueItemFactory->create()->getCollection();
        $pendingItems = $collection->addReadyDependenciesFilter()
                                   ->addActiveProcessFilter()
                                   ->addFieldToFilter('main_table.status', ['in' => $processableStatuses])
                                   ->addOrder('position', 'ASC')
                                   ->setPageSize(1)
                                   ->setCurPage(1)
                                   ->getItems();

        return $pendingItems;
    }

    /**
     * @return array|QueueItemInterface[]
     */
    public function getReadyItems(): array
    {
        /** @var \MageWorx\OpenAI\Model\ResourceModel\QueueItem\Collection $collection */
        $collection = $this->queueItemFactory->create()->getCollection();
        $readyItems = $collection->addReadyDependenciesFilter()
                                 ->addFieldToFilter('status', ['eq' => QueueItemInterface::STATUS_READY])
                                 ->addFieldToFilter('callback', ['neq' => null])
                                 ->addOrder('position', 'ASC')
                                 ->getItems();

        return $readyItems;
    }

    /**
     * Make $item dependent on $dependency items
     * If $dependency items are not completed, $item will not be processed
     * If $dependency items are failed, $item will be failed
     * If $dependency items are completed, $item will be processed
     *
     * @param QueueItemInterface $item
     * @param QueueItemInterface[] $dependency
     * @return void
     */
    public function addDependency(QueueItemInterface $item, array $dependency): void
    {
        $itemId        = $item->getId();
        $dependencyIds = array_map(
            function (QueueItemInterface $item) {
                return $item->getId();
            },
            $dependency
        );

        $this->queueItemResource->createDependency($itemId, $dependencyIds);
    }
}

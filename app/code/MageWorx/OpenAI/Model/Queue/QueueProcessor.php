<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types = 1);

namespace MageWorx\OpenAI\Model\Queue;

use Magento\Framework\Exception\LocalizedException;
use MageWorx\OpenAI\Api\Data\QueueItemInterface;
use MageWorx\OpenAI\Api\QueueItemErrorHandlerPoolInterface;
use MageWorx\OpenAI\Api\QueueItemPreprocessorInterface;
use MageWorx\OpenAI\Api\QueueItemPreprocessorPoolInterface;
use MageWorx\OpenAI\Api\QueueProcessManagementInterface;
use MageWorx\OpenAI\Api\QueueProcessorInterface;
use MageWorx\OpenAI\Api\QueueRepositoryInterface;
use MageWorx\OpenAI\Api\RequestInterfaceFactory;
use MageWorx\OpenAI\Model\Models\ModelsFactory;

class QueueProcessor implements QueueProcessorInterface
{
    private ModelsFactory                      $modelsFactory;
    private RequestInterfaceFactory            $requestFactory;
    private QueueProcessManagementInterface    $queueProcessManagement;
    private QueueItemPreprocessorPoolInterface $queueItemPreprocessorPool;
    private QueueRepositoryInterface           $queueRepository;
    private QueueItemErrorHandlerPoolInterface $errorHandlerPool;

    public function __construct(
        ModelsFactory                      $modelsFactory,
        RequestInterfaceFactory            $requestFactory,
        QueueProcessManagementInterface    $queueProcessManagement,
        QueueItemPreprocessorPoolInterface $queueItemPreprocessorPool,
        QueueRepositoryInterface           $queueRepository,
        QueueItemErrorHandlerPoolInterface $errorHandlerPool
    ) {
        $this->modelsFactory             = $modelsFactory;
        $this->requestFactory            = $requestFactory;
        $this->queueProcessManagement    = $queueProcessManagement;
        $this->queueItemPreprocessorPool = $queueItemPreprocessorPool;
        $this->queueRepository           = $queueRepository;
        $this->errorHandlerPool          = $errorHandlerPool;
    }

    /**
     * Process a single queue item.
     *
     * @param QueueItemInterface $item The queue item to be processed.
     * @return void
     * @throws LocalizedException If processing fails.
     */
    public function processItem(QueueItemInterface $item): void
    {
        try {
            $item = $this->preprocessItem($item);

            $modelType = $item->getModel();
            /** @var \MageWorx\OpenAI\Model\Models\AbstractModel $modelEntity */
            $modelEntity = $this->modelsFactory->create($modelType);

            // Getting data
            $content = $item->getContent();
            $context = $item->getContext();
            $options = $item->getOptions();

            // Building request object
            $request = $this->requestFactory->create();
            $request->setContent($content);
            $request->setPath($modelEntity->getPath());
            $request->setContext($context);

            // Send data to OpenAI using model and save response
            $response = $modelEntity->sendRequest($request, $options);

            // Write response to queue item
            $item->setResponse($response);

            // If error response received, call error handler of this queue item, if exists
            if ($response->getIsError()) {
                $this->callErrorHandlerForItem($item);
            } else {
                // Update queue item status
                $readyStatus = $item->getRequiresApproval() ? QueueItemInterface::STATUS_PENDING_REVIEW : QueueItemInterface::STATUS_READY;
                $item->setStatus($readyStatus);
                $this->queueRepository->save($item);

                // Update process items counter
                $processId = $item->getProcessId();
                if ($processId) {
                    $this->queueProcessManagement->setQueueItemProcessed($processId);
                }
            }
        } catch (\Exception $e) {
            throw new LocalizedException(__('Error processing item: %1', $e->getMessage()));
        }
    }

    /**
     * Preprocess queue item
     *
     * @param QueueItemInterface $item
     * @return QueueItemInterface
     * @throws LocalizedException
     */
    private function preprocessItem(QueueItemInterface $item): QueueItemInterface
    {
        $preprocessorType = $item->getPreprocessor();
        if (!empty($preprocessorType)) {
            try {
                /** @var QueueItemPreprocessorInterface $preprocessorEntity */
                $preprocessorEntity = $this->queueItemPreprocessorPool->getByType($preprocessorType);
            } catch (\Exception $e) {
                throw new LocalizedException(
                    __(
                        'Error creating preprocessor of type %2 for item %3. Error message: %1',
                        $e->getMessage(),
                        $preprocessorType,
                        $item->getId()
                    )
                );
            }
            $preprocessorEntity->execute($item);
        }

        return $item;
    }

    /**
     * When error returned in response we are trying to call error handler of this queue item.
     * If error handler not exists or error handler can't process error, we are marking this queue item as fatal error.
     * Fatal error means that this queue item can't be processed anymore.
     * If error handler exists and can process error, it takes control over this queue item status.
     *
     * @param QueueItemInterface $item
     * @return void
     */
    private function callErrorHandlerForItem(QueueItemInterface $item): void
    {
        $errorHandlerType = $item->getErrorHandler() ?? QueueItemErrorHandlerPoolInterface::DEFAULT_HANDLER_GROUP;

        $successfullyProcessed = $this->errorHandlerPool->process($errorHandlerType, $item);
        if (!$successfullyProcessed) {
            $this->performFatalErrorOfQueueItem($item);
        }
    }

    /**
     * Mark queue item as fatal error (set fatal error status)
     * Fatal error means that this queue item can't be processed anymore.
     *
     * @param QueueItemInterface $item
     * @return void
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    private function performFatalErrorOfQueueItem(QueueItemInterface $item): void
    {
        $item->setStatus(QueueItemInterface::STATUS_FATAL_ERROR);
        $this->queueRepository->save($item);

        // Update process items counter
        $processId = $item->getProcessId();
        if ($processId) {
            $this->queueProcessManagement->setQueueItemProcessed($processId);
        }
    }
}

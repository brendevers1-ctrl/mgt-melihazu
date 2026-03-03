<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types = 1);

namespace MageWorx\OpenAI\Model\Queue\QueueItemErrorHandler;

use MageWorx\OpenAI\Api\Data\QueueItemInterface;
use MageWorx\OpenAI\Api\QueueItemErrorHandlerInterface;
use MageWorx\OpenAI\Model\ResourceModel\QueueItem as QueueItemResource;

class RequestLimitReachedHandler implements QueueItemErrorHandlerInterface
{
    const ERROR_TYPE = 'tokens_usage_based';
    const ERROR_CODE = 'rate_limit_exceeded';

    protected QueueItemResource               $queueItemResource;

    public function __construct(
        QueueItemResource               $queueItemResource
    ) {
        $this->queueItemResource      = $queueItemResource;
    }

    /**
     * @inheritDoc
     */
    public function execute(QueueItemInterface $queueItem): bool
    {
        $response = $queueItem->getResponse();
        if (!$response->getIsError()) {
            return false;
        }

        $content = $response->getChatResponse();
        if (empty($content['error'])) {
            return false;
        }

        if ($content['error']['type'] === static::ERROR_TYPE && $content['error']['code'] === static::ERROR_CODE) {
            // Force change status of queue item to restrict access to it from the queue processor
            $this->queueItemResource->updateStatusAndMoveToTheEnd($queueItem->getId(), QueueItemInterface::STATUS_FAILED);

            return true;
        }

        // Error of this type can not be processed using this error handler
        return false;
    }
}

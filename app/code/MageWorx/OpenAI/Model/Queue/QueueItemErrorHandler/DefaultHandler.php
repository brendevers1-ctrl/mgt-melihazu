<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types = 1);

namespace MageWorx\OpenAI\Model\Queue\QueueItemErrorHandler;

use MageWorx\OpenAI\Api\Data\QueueItemInterface;
use MageWorx\OpenAI\Api\QueueItemErrorHandlerInterface;

class DefaultHandler implements QueueItemErrorHandlerInterface
{
    /**
     * @inheritDoc
     */
    public function execute(QueueItemInterface $queueItem): bool
    {
        return false;
    }
}

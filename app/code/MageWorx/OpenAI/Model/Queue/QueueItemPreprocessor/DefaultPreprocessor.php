<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types = 1);

namespace MageWorx\OpenAI\Model\Queue\QueueItemPreprocessor;

use MageWorx\OpenAI\Api\Data\QueueItemInterface;
use MageWorx\OpenAI\Api\QueueItemPreprocessorInterface;

class DefaultPreprocessor implements QueueItemPreprocessorInterface
{
    /**
     * @param QueueItemInterface $queueItem
     * @return QueueItemInterface
     */
    public function execute(QueueItemInterface $queueItem): QueueItemInterface
    {
        return $queueItem;
    }
}

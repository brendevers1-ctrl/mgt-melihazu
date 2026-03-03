<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types = 1);

namespace MageWorx\OpenAI\Cron\Queue;

use MageWorx\OpenAI\Model\ResourceModel\QueueItem as QueueItemResource;

/**
 * Clear completed queue items older than 3 days
 * @see \MageWorx\OpenAI\Model\ResourceModel\QueueItem::removeCompletedItemsOlderThanThreeDays()
 */
class ClearCompletedQueueItems
{
    protected $resourceModel;

    public function __construct(
        QueueItemResource $resourceModel
    ) {
        $this->resourceModel = $resourceModel;
    }

    public function execute()
    {
        $this->resourceModel->removeCompletedItemsOlderThanThreeDays();
    }
}

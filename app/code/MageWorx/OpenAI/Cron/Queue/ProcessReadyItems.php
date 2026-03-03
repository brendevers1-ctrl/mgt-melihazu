<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types = 1);

namespace MageWorx\OpenAI\Cron\Queue;

use MageWorx\OpenAI\Api\QueueManagementInterface;

class ProcessReadyItems
{
    protected QueueManagementInterface $queueManagement;

    public function __construct(
        QueueManagementInterface $queueManagement
    ) {
        $this->queueManagement = $queueManagement;
    }

    public function execute(): void
    {
        // Process ready items from queue
        $this->queueManagement->processReadyItems();
    }
}

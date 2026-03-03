<?php

namespace MageWorx\OpenAI\Cron\Queue;

use MageWorx\OpenAI\Api\QueueManagementInterface;
use MageWorx\OpenAI\Exception\QueueEmptyException;

class Tick
{
    protected QueueManagementInterface $queueManagement;

    public function __construct(
        QueueManagementInterface $queueManagement
    ) {
        $this->queueManagement = $queueManagement;
    }

    public function execute(): void
    {
        $startTime = time();

        while (true) {
            // Process one item from the queue
            try {
                $this->queueManagement->processNextItem();
            } catch (QueueEmptyException $e) {
                // Empty queue
                break;
            }

            if (time() - $startTime > 60) {
                break;
            }
        }
    }
}

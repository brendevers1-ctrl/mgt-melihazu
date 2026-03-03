<?php

declare(strict_types=1);

namespace MageWorx\OpenAI\Api;

use MageWorx\OpenAI\Api\Data\QueueItemInterface;

/**
 * Interface for queue item processing.
 */
interface QueueProcessorInterface
{
    /**
     * Process a single queue item.
     *
     * @param QueueItemInterface $item The queue item to be processed.
     * @return void
     */
    public function processItem(QueueItemInterface $item): void;
}

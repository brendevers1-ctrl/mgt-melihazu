<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types = 1);

namespace MageWorx\OpenAI\Api;

use MageWorx\OpenAI\Api\Data\QueueItemInterface;

interface QueueItemErrorHandlerInterface
{
    /**
     * Execute error handler.
     * Return true if error was handled.
     * Return false if error was not handled.
     *
     * @param QueueItemInterface $queueItem
     * @return bool
     */
    public function execute(QueueItemInterface $queueItem): bool;
}

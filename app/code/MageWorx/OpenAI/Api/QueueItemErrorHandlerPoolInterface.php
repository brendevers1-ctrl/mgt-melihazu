<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types = 1);

namespace MageWorx\OpenAI\Api;

use MageWorx\OpenAI\Api\Data\QueueItemInterface;

interface QueueItemErrorHandlerPoolInterface
{
    const DEFAULT_HANDLER_GROUP = 'default';

    /**
     * Retrieve error handler by type
     *
     * @param string $type
     * @return QueueItemErrorHandlerInterface[]
     */
    public function getByType(string $type): array;

    /**
     * Process error.
     * Return true if error was handled.
     * Return false if error was not handled.
     *
     * @param string $type
     * @param QueueItemInterface $queueItem
     * @return bool
     */
    public function process(string $type, QueueItemInterface $queueItem): bool;
}

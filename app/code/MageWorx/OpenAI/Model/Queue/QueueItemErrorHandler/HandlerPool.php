<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types = 1);

namespace MageWorx\OpenAI\Model\Queue\QueueItemErrorHandler;

use MageWorx\OpenAI\Api\Data\QueueItemInterface;
use MageWorx\OpenAI\Api\QueueItemErrorHandlerPoolInterface;

class HandlerPool implements QueueItemErrorHandlerPoolInterface
{
    protected array $handlers = [];

    public function __construct(
        array $handlers = []
    ) {
        $this->handlers = $handlers;
    }

    /**
     * @inheritDoc
     */
    public function getByType(string $type): array
    {
        return $this->handlers[$type] ?? [];
    }

    /**
     * @inheritDoc
     */
    public function process(string $type, QueueItemInterface $queueItem): bool
    {
        $handlers = $this->getByType($type);
        foreach ($handlers as $handler) {
            $handled = $handler->execute($queueItem);
            if ($handled) {
                return true;
            }
        }

        return false;
    }
}

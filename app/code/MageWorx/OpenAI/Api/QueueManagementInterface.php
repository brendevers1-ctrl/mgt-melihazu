<?php

declare(strict_types = 1);

namespace MageWorx\OpenAI\Api;

use MageWorx\OpenAI\Api\Data\QueueItemInterface;
use MageWorx\OpenAI\Api\Data\QueueProcessInterface;

/**
 * Queue Management Interface for MageWorx OpenAI Module
 *
 * Interface for managing the queue of requests to the OpenAI API.
 */
interface QueueManagementInterface
{
    /**
     * @param string $content
     * @param OptionsInterface $options
     * @param string|null $callbackModel
     * @param array|null $context
     * @param QueueProcessInterface|null $process
     * @param array|null $additionalData
     * @param string|null $preprocessor
     * @param string|null $errorHandler
     * @return QueueItemInterface
     */
    public function addToQueue(
        string                 $content,
        OptionsInterface       $options,
        ?string                $callbackModel,
        ?array                 $context = [],
        ?QueueProcessInterface $process = null,
        ?array                 $additionalData = [],
        ?string                $preprocessor = null,
        ?string                $errorHandler = null,
        ?bool                  $requiresApproval = false
    ): QueueItemInterface;

    /**
     * Process the next item in the queue.
     *
     * @return void
     */
    public function processNextItem(): void;

    /**
     * Process all items in the queue.
     *
     * @return void
     */
    public function processAll(): void;

    /**
     * Logic to process all ready items in the queue
     *
     * @return void
     */
    public function processReadyItems(): void;

    /**
     * @param QueueItemInterface $item
     * @return void
     */
    public function processItemCallback(QueueItemInterface $item): void;

    /**
     * Retrieve all pending items in the queue.
     *
     * @return QueueItemInterface[] Array of pending queue items.
     */
    public function getPendingItems(): array;

    /**
     * Retrieve all ready items in the queue.
     *
     * @return QueueItemInterface[] Array of completed queue items.
     */
    public function getReadyItems(): array;

    /**
     * Make $item dependent on $dependency items
     * If $dependency items are not completed, $item will not be processed
     * If $dependency items are failed, $item will be failed
     * If $dependency items are completed, $item will be processed
     *
     * @param QueueItemInterface $item
     * @param QueueItemInterface[] $dependency
     * @return void
     */
    public function addDependency(QueueItemInterface $item, array $dependency): void;
}

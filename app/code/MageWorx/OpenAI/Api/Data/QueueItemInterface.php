<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types = 1);

namespace MageWorx\OpenAI\Api\Data;

use MageWorx\OpenAI\Api\OptionsInterface;
use MageWorx\OpenAI\Api\ResponseInterface;

/**
 * Queue Item Interface for MageWorx OpenAI Module
 *
 * Interface for queue item entity. Defines getters and setters for the queue item properties.
 */
interface QueueItemInterface
{
    // Queue item statuses constants:
    /**
     * Pending queue item. Should be processed by cron.
     */
    public const STATUS_PENDING = 0;

    /**
     * Processing queue item. Processed by cronjob right now.
     */
    public const STATUS_PROCESSING = 1;

    /**
     * Ready queue item. Should be processed by callback.
     */
    public const STATUS_READY = 2;

    /**
     * Completed queue item. Processed by callback.
     */
    public const STATUS_COMPLETED = 3;

    /**
     * Failed queue item. Error response from chatgpt. Should be re-run by cron and processed by callback after success.
     */
    public const STATUS_FAILED = 4;

    /**
     * Callback failed queue item. Error response from callback. Should be re-run by cron and processed by callback after success.
     */
    public const STATUS_CALLBACK_FAILED = 5;

    /**
     * Fatal error with queue item, which can not be processed by cron or callback. Prevents queue item from processing.
     */
    public const STATUS_FATAL_ERROR = 6;

    /**
     * The status specifies that the queue item should not be processed because a third party process has taken control
     * over it and another process of the queue item is being created, or it can be processed in some other way.
     */
    public const STATUS_LOCKED = 7;

    /**
     * The status indicates that the queue item's result was rejected by the client. Will not be processed by callback.
     */
    public const STATUS_DENIED = 8;

    /**
     * The status indicates that the queue item is pending review by a human. Locked for the callback processor
     * until the status is changed.
     */
    public const STATUS_PENDING_REVIEW = 9;

    /**
     * Get item ID
     *
     * @return int|null
     */
    public function getId(): ?int;

    /**
     * Get content
     *
     * @return string
     */
    public function getContent(): string;

    /**
     * Get context
     *
     * @return array|null
     */
    public function getContext(): ?array;

    /**
     * Get options
     *
     * @return OptionsInterface
     */
    public function getOptions(): OptionsInterface;

    /**
     * Get status
     *
     * @return int
     */
    public function getStatus(): int;

    /**
     * Get response
     *
     * @return ResponseInterface
     */
    public function getResponse(): ResponseInterface;

    /**
     * Get process id
     *
     * @return int|null
     */
    public function getProcessId(): ?int;

    /**
     * Get request id
     *
     * @return int|null
     */
    public function getRequestDataId(): ?int;

    /**
     * @return int
     */
    public function getPosition(): int;

    /**
     * @return string
     */
    public function getModel(): string;

    /**
     * @return string|null
     */
    public function getCallback(): ?string;

    /**
     * Additional data as product_id, review_id, store_id etc.
     * Stored as JSON.
     *
     * @return array
     */
    public function getAdditionalData(): array;

    /**
     * Set content
     *
     * @param string $content
     * @return QueueItemInterface
     */
    public function setContent(string $content): QueueItemInterface;

    /**
     * Set context
     *
     * @param array|null $context
     * @return QueueItemInterface
     */
    public function setContext(?array $context): QueueItemInterface;

    /**
     * Set options
     *
     * @param OptionsInterface $options
     * @return QueueItemInterface
     */
    public function setOptions(OptionsInterface $options): QueueItemInterface;

    /**
     * Set status
     *
     * @param int $status
     * @return QueueItemInterface
     */
    public function setStatus(int $status): QueueItemInterface;

    /**
     * Set response
     *
     * @param ResponseInterface $response
     * @return QueueItemInterface
     */
    public function setResponse(ResponseInterface $response): QueueItemInterface;

    /**
     * Set process id
     *
     * @param int|null $id
     * @return QueueItemInterface
     */
    public function setProcessId(?int $id): QueueItemInterface;

    /**
     * Set request id
     *
     * @param int|null $id
     * @return QueueItemInterface
     */
    public function setRequestDataId(?int $id): QueueItemInterface;

    /**
     * Set position
     *
     * @param int $position
     * @return QueueItemInterface
     */
    public function setPosition(int $position): QueueItemInterface;

    /**
     * @param string $model
     * @return QueueItemInterface
     */
    public function setModel(string $model): QueueItemInterface;

    /**
     * @param string|null $callback
     * @return QueueItemInterface
     */
    public function setCallback(?string $callback): QueueItemInterface;

    /**
     * Additional data as product_id, review_id, store_id etc.
     * Stored as JSON.
     *
     * @param array $data
     * @return QueueItemInterface
     */
    public function setAdditionalData(array $data): QueueItemInterface;

    /**
     * Get type of preprocessor for queue item
     *
     * @return string|null
     * @see \MageWorx\OpenAI\Api\QueueItemPreprocessorInterface
     */
    public function getPreprocessor(): ?string;

    /**
     * Set type of preprocessor for queue item
     *
     * @param string|null $preprocessor
     * @return QueueItemInterface
     * @see \MageWorx\OpenAI\Api\QueueItemPreprocessorInterface
     */
    public function setPreprocessor(?string $preprocessor): QueueItemInterface;

    /**
     * Error handler for request
     *
     * @return string|null
     * @see \MageWorx\OpenAI\Api\QueueItemErrorHandlerInterface
     */
    public function getErrorHandler(): ?string;

    /**
     * Set error handler for request
     * @param string|null $errorHandler
     * @return QueueItemInterface
     * @see \MageWorx\OpenAI\Api\QueueItemErrorHandlerInterface
     */
    public function setErrorHandler(?string $errorHandler): QueueItemInterface;

    /**
     * Indicates whether approval is required or not.
     * If true, the queue item will be processed only after the approval of the administrator (human).
     *
     * @return bool Indicates whether approval is required or not
     */
    public function getRequiresApproval(): bool;

    /**
     * Set whether the queue item requires approval.
     * If true, the queue item will be processed only after the approval of the administrator (human).
     *
     * @param bool $value The value indicating whether the queue item requires approval
     *
     * @return QueueItemInterface The updated QueueItemInterface instance
     */
    public function setRequiresApproval(bool $value): QueueItemInterface;
}

<?php

declare(strict_types = 1);

namespace MageWorx\OpenAI\Api\Data;

/**
 * Interface for MW Queue Process entity.
 */
interface QueueProcessInterface
{
    /**
     * Constants for keys of data array.
     */
    const ENTITY_ID       = 'entity_id';
    const NAME            = 'name';
    const TYPE            = 'type';
    const CODE            = 'code';
    const SIZE            = 'size';
    const PROCESSED       = 'processed';
    const CREATED_AT      = 'created_at';
    const UPDATED_AT      = 'updated_at';
    const ADDITIONAL_DATA = 'additional_data';
    const STATUS          = 'status';
    const MODULE          = 'module';

    const STATUS_ENABLED  = 1;
    const STATUS_DISABLED = 0;

    /**
     * Get Entity ID.
     *
     * @return int|null
     */
    public function getEntityId();

    /**
     * Set Entity ID.
     *
     * @param int $entityId
     * @return QueueProcessInterface
     */
    public function setEntityId($entityId): self;

    /**
     * Get process name.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Set process name.
     *
     * @param string $name
     * @return QueueProcessInterface
     */
    public function setName(string $name): self;

    /**
     * Get size.
     *
     * @return int
     */
    public function getSize(): int;

    /**
     * Set size.
     *
     * @param int $size
     * @return QueueProcessInterface
     */
    public function setSize(int $size): self;

    /**
     * Get processed.
     *
     * @return int
     */
    public function getProcessed(): int;

    /**
     * Set processed.
     *
     * @param int $processed
     * @return QueueProcessInterface
     */
    public function setProcessed(int $processed): self;

    /**
     * Get created at timestamp.
     *
     * @return string|null
     */
    public function getCreatedAt(): ?string;

    /**
     * Set created at timestamp.
     *
     * @param string $createdAt
     * @return QueueProcessInterface
     */
    public function setCreatedAt(string $createdAt): self;

    /**
     * Get updated at timestamp.
     *
     * @return string|null
     */
    public function getUpdatedAt(): ?string;

    /**
     * Set updated at timestamp.
     *
     * @param string $updatedAt
     * @return QueueProcessInterface
     */
    public function setUpdatedAt(string $updatedAt): self;

    /**
     * Get additional data.
     *
     * @return string|null
     */
    public function getAdditionalData(): ?string;

    /**
     * Set additional data.
     *
     * @param string|null $additionalData
     * @return QueueProcessInterface
     */
    public function setAdditionalData(?string $additionalData): self;

    /**
     * Get the type of the object.
     * Queue processes can have same or different types based on the initial action.
     * For example, the type of the queue process for generating product descriptions is "seo_ai_product_description".
     *
     * @return string|null The type of the object if it is set, or null otherwise.
     */
    public function getType(): ?string;

    /**
     * Set the type of the object.
     * Queue processes can have same or different types based on the initial action.
     * For example, the type of the queue process for generating product descriptions is "seo_ai_product_description".
     *
     * @param string $type The type must be a string.
     * @return self Returns the updated object.
     */
    public function setType(string $type): self;

    /**
     * Get the unique code of the process.
     *
     * @return string|null The code, or null if no code is set.
     */
    public function getCode(): ?string;

    /**
     * Sets the unique code of the process.
     *
     * @param string $code The code to set.
     * @return self The current object.
     */
    public function setCode(string $code): self;

    /**
     * Get process status.
     * Available statuses: 0 - disabled, 1 - enabled.
     *
     * @return int
     */
    public function getStatus(): int;

    /**
     * Set process status.
     * Available statuses: 0 - disabled, 1 - enabled.
     *
     * @param int $status
     * @return self
     */
    public function setStatus(int $status): self;

    /**
     * Get the code of the module which is initialized this process.
     *
     * @return string
     */
    public function getModule(): ?string;

    /**
     * Get the code of the module which is initializing this process.
     *
     * @param string $module
     * @return self
     */
    public function setModule(string $module): self;
}

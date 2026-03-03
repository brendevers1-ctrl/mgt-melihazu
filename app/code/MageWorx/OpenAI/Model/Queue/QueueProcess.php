<?php

declare(strict_types=1);

namespace MageWorx\OpenAI\Model\Queue;

use Magento\Framework\Model\AbstractExtensibleModel;
use MageWorx\OpenAI\Api\Data\QueueProcessInterface;
use MageWorx\OpenAI\Model\ResourceModel\QueueProcess as ResourceModel;

class QueueProcess extends AbstractExtensibleModel implements QueueProcessInterface
{
    /**
     * Initialize resource model
     */
    protected function _construct()
    {
        $this->_init(ResourceModel::class);
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return parent::getId() ? (int)parent::getId(): null;
    }

    /**
     * @return int|null
     */
    public function getEntityId()
    {
        return (int)$this->_getData(self::ENTITY_ID) ?: null;
    }

    public function setEntityId($entityId): QueueProcessInterface
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }

    public function getName(): string
    {
        return (string)$this->_getData(self::NAME);
    }

    public function setName(string $name): QueueProcessInterface
    {
        return $this->setData(self::NAME, $name);
    }

    public function getSize(): int
    {
        return (int)$this->_getData(self::SIZE);
    }

    public function setSize(int $size): QueueProcessInterface
    {
        return $this->setData(self::SIZE, $size);
    }

    public function getProcessed(): int
    {
        return (int)$this->_getData(self::PROCESSED);
    }

    public function setProcessed(int $processed): QueueProcessInterface
    {
        return $this->setData(self::PROCESSED, $processed);
    }

    public function getCreatedAt(): ?string
    {
        return $this->_getData(self::CREATED_AT);
    }

    public function setCreatedAt(string $createdAt): QueueProcessInterface
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    public function getUpdatedAt(): ?string
    {
        return $this->_getData(self::UPDATED_AT);
    }

    public function setUpdatedAt(string $updatedAt): QueueProcessInterface
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }

    public function getAdditionalData(): ?string
    {
        return $this->_getData(self::ADDITIONAL_DATA);
    }

    public function setAdditionalData(?string $additionalData): QueueProcessInterface
    {
        return $this->setData(self::ADDITIONAL_DATA, $additionalData);
    }

    /**
     * @inheritDoc
     */
    public function getType(): ?string
    {
        return $this->_getData(self::TYPE);
    }

    /**
     * @inheritDoc
     */
    public function setType(string $type): QueueProcessInterface
    {
        return $this->setData(self::TYPE, $type);
    }

    /**
     * @inheritDoc
     */
    public function getCode(): ?string
    {
        return $this->_getData(self::CODE);
    }

    /**
     * @inheritDoc
     */
    public function setCode(string $code): QueueProcessInterface
    {
        return $this->setData(self::CODE, $code);
    }

    /**
     * @inheritDoc
     */
    public function getStatus(): int
    {
        return (int)$this->_getData(self::STATUS);
    }

    /**
     * @inheritDoc
     */
    public function setStatus(int $status): QueueProcessInterface
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * @inheritDoc
     */
    public function getModule(): ?string
    {
        return $this->_getData(self::MODULE);
    }

    /**
     * @inheritDoc
     */
    public function setModule(string $module): QueueProcessInterface
    {
        return $this->setData(self::MODULE, $module);
    }
}

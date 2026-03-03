<?php

declare(strict_types = 1);

namespace MageWorx\OpenAI\Model\Queue;

use Magento\Framework\Model\AbstractExtensibleModel;
use MageWorx\OpenAI\Api\Data\QueueItemInterface;
use MageWorx\OpenAI\Api\OptionsInterface;
use MageWorx\OpenAI\Api\ResponseInterface;
use MageWorx\OpenAI\Model\ResourceModel\QueueItem as QueueItemResource;

/**
 * Queue Item Model for MageWorx OpenAI Module
 *
 * Represents a queue item entity, providing getters and setters for queue item properties.
 */
class QueueItem extends AbstractExtensibleModel implements QueueItemInterface
{
    /**
     * Initialize resource model
     */
    protected function _construct()
    {
        $this->_init(QueueItemResource::class);
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return parent::getId() ? (int)parent::getId() : null;
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent(): string
    {
        return $this->_getData('content');
    }

    /**
     * Get context
     *
     * @return array|null
     */
    public function getContext(): ?array
    {
        return $this->_getData('context');
    }

    /**
     * Get options
     *
     * @return OptionsInterface
     */
    public function getOptions(): OptionsInterface
    {
        return $this->_getData('options');
    }

    /**
     * Get status
     *
     * @return int
     */
    public function getStatus(): int
    {
        return $this->_getData('status');
    }

    /**
     * Get response
     *
     * @return ResponseInterface
     */
    public function getResponse(): ResponseInterface
    {
        return $this->_getData('response');
    }

    /**
     * Set content
     *
     * @param string $content
     * @return QueueItemInterface
     */
    public function setContent(string $content): QueueItemInterface
    {
        return $this->setData('content', $content);
    }

    /**
     * Set context
     *
     * @param array|null $context
     * @return QueueItemInterface
     */
    public function setContext(?array $context): QueueItemInterface
    {
        return $this->setData('context', $context);
    }

    /**
     * Set options
     *
     * @param OptionsInterface $options
     * @return QueueItemInterface
     */
    public function setOptions(OptionsInterface $options): QueueItemInterface
    {
        return $this->setData('options', $options);
    }

    /**
     * Set status
     *
     * @param int $status
     * @return QueueItemInterface
     */
    public function setStatus(int $status): QueueItemInterface
    {
        return $this->setData('status', $status);
    }

    /**
     * Set response
     *
     * @param ResponseInterface $response
     * @return QueueItemInterface
     */
    public function setResponse(ResponseInterface $response): QueueItemInterface
    {
        return $this->setData('response', $response);
    }

    /**
     * @inheritDoc
     */
    public function getProcessId(): ?int
    {
        return $this->_getData('process_id') !== null ? (int)$this->_getData('process_id') : null;
    }

    /**
     * @inheritDoc
     */
    public function getRequestDataId(): ?int
    {
        return $this->_getData('request_data_id') !== null ? (int)$this->_getData('request_data_id') : null;
    }

    /**
     * @inheritDoc
     */
    public function setProcessId(?int $id): QueueItemInterface
    {
        return $this->setData('process_id', $id);
    }

    /**
     * @inheritDoc
     */
    public function setRequestDataId(?int $id): QueueItemInterface
    {
        return $this->setData('request_data_id', $id);
    }

    /**
     * @inheritDoc
     */
    public function getPosition(): int
    {
        return $this->_getData('position');
    }

    /**
     * @inheritDoc
     */
    public function getModel(): string
    {
        return $this->_getData('model');
    }

    /**
     * @inheritDoc
     */
    public function getCallback(): ?string
    {
        return $this->_getData('callback');
    }

    /**
     * @inheritDoc
     */
    public function setPosition(int $position): QueueItemInterface
    {
        return $this->setData('position', $position);
    }

    /**
     * @inheritDoc
     */
    public function setModel(string $model): QueueItemInterface
    {
        return $this->setData('model', $model);
    }

    /**
     * @inheritDoc
     */
    public function setCallback(?string $callback): QueueItemInterface
    {
        return $this->setData('callback', $callback);
    }

    /**
     * @inheritDoc
     */
    public function getAdditionalData(): array
    {
        return $this->_getData('additional_data');
    }

    /**
     * @inheritDoc
     */
    public function setAdditionalData(array $data): QueueItemInterface
    {
        return $this->setData('additional_data', $data);
    }

    /**
     * @inheritDoc
     */
    public function getPreprocessor(): ?string
    {
        return $this->_getData('preprocessor');
    }

    /**
     * @inheritDoc
     */
    public function setPreprocessor(?string $preprocessor): QueueItemInterface
    {
        return $this->setData('preprocessor', $preprocessor);
    }

    /**
     * @inheritDoc
     */
    public function getErrorHandler(): ?string
    {
        return $this->_getData('error_handler');
    }

    /**
     * @inheritDoc
     */
    public function setErrorHandler(?string $errorHandler): QueueItemInterface
    {
        return $this->setData('error_handler', $errorHandler);
    }

    /**
     * @inheritDoc
     */
    public function getRequiresApproval(): bool
    {
        return (bool)$this->_getData('requires_approval');
    }

    /**
     * @inheritDoc
     */
    public function setRequiresApproval(bool $value): QueueItemInterface
    {
        return $this->setData('requires_approval', $value);
    }
}

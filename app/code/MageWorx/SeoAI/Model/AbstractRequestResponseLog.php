<?php

namespace MageWorx\SeoAI\Model;

use Magento\Framework\Model\AbstractExtensibleModel;
use MageWorx\SeoAI\Api\Data\RequestResponseLogInterface;

abstract class AbstractRequestResponseLog extends AbstractExtensibleModel
implements RequestResponseLogInterface
{
    /**
     * Which we are generating (attribute name) type.
     * @param string $value
     * @return RequestResponseLogInterface
     * @see \MageWorx\SeoAI\Model\Source\ProductMessageType
     *
     */
    public function setMessageType(string $value): RequestResponseLogInterface
    {
        return $this->setData('message_type', $value);
    }

    /**
     * Which we are generating (attribute name) type.
     * @return string
     * @see \MageWorx\SeoAI\Model\Source\ProductMessageType
     */
    public function getMessageType(): string
    {
        return $this->getData('message_type');
    }

    /**
     * Full request message
     *
     * @param string $value
     * @return RequestResponseLogInterface
     */
    public function setRequestMessage(string $value): RequestResponseLogInterface
    {
        return $this->setData('request_message', $value);
    }

    /**
     * Fell request message
     *
     * @return string
     */
    public function getRequestMessage(): string
    {
        return $this->getData('request_message');
    }

    /**
     * Context of request. Stored in DB as JSON encoded value.
     *
     * @param array $context
     * @return RequestResponseLogInterface
     */
    public function setContext(array $context): RequestResponseLogInterface
    {
        return $this->setData('context', $context);
    }

    /**
     * Context of request. Stored in DB as JSON encoded value.
     *
     * @return array
     */
    public function getContext(): array
    {
        return $this->getData('context');
    }

    /**
     * Response.
     *
     * @param string $value
     * @return RequestResponseLogInterface
     */
    public function setResponseMessage(string $value): RequestResponseLogInterface
    {
        return $this->setData('response_message', $value);
    }

    /**
     * Response.
     *
     * @return string
     */
    public function getResponseMessage(): string
    {
        return $this->getData('response_message');
    }

    /**
     * Index of variant.
     *
     * @param int $value
     * @return RequestResponseLogInterface
     */
    public function setVariantIndex(int $value): RequestResponseLogInterface
    {
        return $this->setData('variant_index', $value);
    }

    /**
     * Index of variant.
     *
     * @return int
     */
    public function getVariantIndex(): int
    {
        return $this->getData('variant_index');
    }

    /**
     * Created at date.
     *
     * @param \DateTimeInterface $value
     * @return RequestResponseLogInterface
     */
    public function setCreatedAt(\DateTimeInterface $value): RequestResponseLogInterface
    {
        return $this->setData('created_at', $value);
    }

    /**
     * Created at date.
     *
     * @return string
     */
    public function getCreatedAt(): string
    {
        return $this->getData('created_at');
    }

    /**
     * Was the result good?
     * NULL - means not rated yet.
     *
     * @return bool|null
     */
    public function getIsOk(): ?bool
    {
        return $this->getData('is_ok');
    }

    /**
     * Was the result good?
     * NULL - means not rated yet.
     *
     * @param bool|null $value
     * @return RequestResponseLogInterface
     */
    public function setIsOk(?bool $value): RequestResponseLogInterface
    {
        return $this->setData('is_ok', $value);
    }

    /**
     * Was result applied?
     * Default - false.
     *
     * @return bool
     */
    public function getIsApplied(): bool
    {
        return (bool)$this->getData('is_applied');
    }

    /**
     * Was result applied?
     * Default - false.
     *
     * @param bool $value
     * @return RequestResponseLogInterface
     */
    public function setIsApplied(bool $value): RequestResponseLogInterface
    {
        return $this->setData('is_applied', $value);
    }

    /**
     * Approximate number of tokens used for request and response.
     *
     * @return int
     */
    public function getApproximateNumberOfTokens(): int
    {
        return (int)$this->getData('approximate_number_of_tokens');
    }

    /**
     * Approximate number of tokens used for request and response.
     *
     * @param int $value
     * @return RequestResponseLogInterface
     */
    public function setApproximateNumberOfTokens(int $value): RequestResponseLogInterface
    {
        return $this->setData('approximate_number_of_tokens', $value);
    }
}

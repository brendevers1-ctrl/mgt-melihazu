<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types = 1);

namespace MageWorx\SeoAI\Api\Data;

interface RequestResponseLogInterface
{
    /**
     * Which we are generating (attribute name) type.
     * @param string $value
     * @return RequestResponseLogInterface
     *@see \MageWorx\SeoAI\Model\Source\ProductMessageType
     *
     */
    public function setMessageType(string $value): RequestResponseLogInterface;

    /**
     * Which we are generating (attribute name) type.
     * @return string
     * @see \MageWorx\SeoAI\Model\Source\ProductMessageType
     */
    public function getMessageType(): string;

    /**
     * Full request message
     *
     * @param string $value
     * @return RequestResponseLogInterface
     */
    public function setRequestMessage(string $value): RequestResponseLogInterface;

    /**
     * Fell request message
     *
     * @return string
     */
    public function getRequestMessage(): string;

    /**
     * Context of request. Stored in DB as JSON encoded value.
     *
     * @param array $context
     * @return RequestResponseLogInterface
     */
    public function setContext(array $context): RequestResponseLogInterface;

    /**
     * Context of request. Stored in DB as JSON encoded value.
     *
     * @return array
     */
    public function getContext(): array;

    /**
     * Response.
     *
     * @param string $value
     * @return RequestResponseLogInterface
     */
    public function setResponseMessage(string $value): RequestResponseLogInterface;

    /**
     * Response.
     *
     * @return string
     */
    public function getResponseMessage(): string;

    /**
     * Index of variant.
     *
     * @param int $value
     * @return RequestResponseLogInterface
     */
    public function setVariantIndex(int $value): RequestResponseLogInterface;

    /**
     * Index of variant.
     *
     * @return int
     */
    public function getVariantIndex(): int;

    /**
     * Created at date.
     *
     * @param \DateTimeInterface $value
     * @return RequestResponseLogInterface
     */
    public function setCreatedAt(\DateTimeInterface $value): RequestResponseLogInterface;

    /**
     * Created at date.
     *
     * @return string
     */
    public function getCreatedAt(): string;

    /**
     * Was the result good?
     * NULL - means not rated yet.
     *
     * @return bool|null
     */
    public function getIsOk(): ?bool;

    /**
     * Was the result good?
     * NULL - means not rated yet.
     *
     * @param bool|null $value
     * @return RequestResponseLogInterface
     */
    public function setIsOk(?bool $value): RequestResponseLogInterface;

    /**
     * Was result applied?
     * Default - false.
     *
     * @return bool
     */
    public function getIsApplied(): bool;

    /**
     * Was result applied?
     * Default - false.
     *
     * @param bool $value
     * @return RequestResponseLogInterface
     */
    public function setIsApplied(bool $value): RequestResponseLogInterface;

    /**
     * Approximate number of tokens used for request and response.
     *
     * @return int
     */
    public function getApproximateNumberOfTokens(): int;

    /**
     * Approximate number of tokens used for request and response.
     *
     * @param int $value
     * @return RequestResponseLogInterface
     */
    public function setApproximateNumberOfTokens(int $value): RequestResponseLogInterface;
}

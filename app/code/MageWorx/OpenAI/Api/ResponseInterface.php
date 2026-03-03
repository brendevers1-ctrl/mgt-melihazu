<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types = 1);

namespace MageWorx\OpenAI\Api;

use JsonSerializable;

interface ResponseInterface extends JsonSerializable
{
    /**
     * Set main content
     *
     * @param string $content
     * @return ResponseInterface
     */
    public function setContent(string $content): ResponseInterface;

    /**
     * Get main content
     *
     * @return string
     */
    public function getContent(): string;

    /**
     * Set RAW OpenAI response
     *
     * @param array $response
     * @return ResponseInterface
     */
    public function setChatResponse(array $response): ResponseInterface;

    /**
     * Get RAW OpenAI response
     *
     * @return array
     */
    public function getChatResponse(): array;

    /**
     * Is error in response.
     *
     * @param bool $isError
     * @return ResponseInterface
     */
    public function setIsError(bool $isError): ResponseInterface;

    /**
     * Is error in response.
     *
     * @return bool
     */
    public function getIsError(): bool;

    /**
     * Populate the response object properties from an array
     *
     * @param array $data Associative array with keys corresponding to the response object properties
     * @return ResponseInterface Returns the current instance of the ResponseInterface
     */
    public function fromArray(array $data): ResponseInterface;
}

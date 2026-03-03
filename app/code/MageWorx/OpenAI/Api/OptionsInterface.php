<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types = 1);

namespace MageWorx\OpenAI\Api;

use Magento\Framework\Exception\InputException;

interface OptionsInterface
{
    /** DEFAULT VALUES */
    const DEFAULT_HEADERS                  = [
        'Content-Type: application/json'
    ];
    const UNSAFE_HEADERS                   = [
        'Authorization'
    ];
    const DEFAULT_MODEL                    = 'gpt-3.5-turbo';
    const DEFAULT_MAX_TOKENS               = 128;
    const DEFAULT_NUMBER_OF_RESULT_OPTIONS = 1;
    const DEFAULT_TEMPERATURE              = 1;
    const DEFAULT_PATH                     = 'v1/chat/completions';

    const TEMP_LOWEST  = 0;
    const TEMP_HIGHEST = 2;

    const HTTP_METHOD_GET    = 'GET';
    const HTTP_METHOD_POST   = 'POST';
    const HTTP_METHOD_PUT    = 'PUT';
    const HTTP_METHOD_DELETE = 'DELETE';

    /**
     * Get current headers
     *
     * @return array
     */
    public function getHeaders(): array;

    /**
     * Set new headers (remove existing)
     *
     * @param array $headers
     * @return OptionsInterface
     */
    public function setHeaders(array $headers): OptionsInterface;

    /**
     * Add headers to existing headers list
     *
     * @param array $headers
     * @return OptionsInterface
     */
    public function addHeaders(array $headers): OptionsInterface;

    /**
     * Get selected model
     *
     * @return string
     */
    public function getModel(): string;

    /**
     * Set model to use
     *
     * @param string $model
     * @return OptionsInterface
     */
    public function setModel(string $model): OptionsInterface;

    /**
     * Initialize default options
     *
     * @return OptionsInterface
     */
    public function initDefault(): OptionsInterface;

    /**
     * Get Number of variants returned by OpenAI model (default 1)
     *
     * @return int
     */
    public function getNumberOfResultOptions(): int;

    /**
     * Set Number of variants returned by OpenAI model (default 1)
     *
     * @param int $value
     * @return OptionsInterface
     */
    public function setNumberOfResultOptions(int $value): OptionsInterface;

    /**
     * What sampling temperature to use, between 0 and 2.
     * Higher values like 0.8 will make the output more random, while lower values like 0.2
     * will make it more focused and deterministic.
     *
     * @return float
     */
    public function getTemperature(): float;

    /**
     * Correct value between 0 and 2.
     *
     * @param float $value
     * @return OptionsInterface
     */
    public function setTemperature(float $value): OptionsInterface;

    /**
     * The maximum number of tokens to generate in the completion.
     * The token count of your prompt plus max_tokens cannot exceed the model's context length.
     *
     * @return int
     */
    public function getMaxTokens(): int;

    /**
     * The maximum number of tokens to generate in the completion.
     * The token count of your prompt plus max_tokens cannot exceed the model's context length.
     *
     * @param int $value
     * @return OptionsInterface
     * @throws InputException
     */
    public function setMaxTokens(int $value): OptionsInterface;

    /**
     * Set path for request
     *
     * @param string $value
     * @return OptionsInterface
     */
    public function setPath(string $value): OptionsInterface;

    /**
     * Get path for request
     *
     * @return string
     */
    public function getPath(): string;

    /**
     * @return string|null
     */
    public function getHttpMethod(): ?string;

    /**
     * @param string|null $value
     * @return OptionsInterface
     */
    public function setHttpMethod(?string $value): OptionsInterface;

    /**
     * @return string|null
     */
    public function getCustomKey(): ?string;

    /**
     * @param string|null $value
     * @return OptionsInterface
     */
    public function setCustomKey(?string $value): OptionsInterface;

    /**
     * Convert options to array
     *
     * @return array
     */
    public function toArray(): array;
}

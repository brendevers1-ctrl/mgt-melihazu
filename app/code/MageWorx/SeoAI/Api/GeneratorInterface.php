<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types = 1);

namespace MageWorx\SeoAI\Api;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

interface GeneratorInterface
{
    /**
     * Generate something using OpenAI API
     *
     * @param int $entityId
     * @param int $storeId
     * @param string $modelType
     * @param array $selectedAttributes
     * @param float $temperature
     * @param int $variants
     * @return string
     */
    public function execute(
        int    $entityId,
        int    $storeId,
        string $modelType,
        array  $selectedAttributes,
        float  $temperature,
        int    $variants
    ): string;

    /**
     * @param int $entityId
     * @param string $modelType
     * @param float $temperature
     * @param int $variants
     * @param string $content
     * @param array $context
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function executeWithPregeneratedRequest(
        int    $entityId,
        string $modelType,
        float  $temperature,
        int    $variants,
        string $content,
        array  $context
    ): string;

    /**
     * Add a task to the queue for generating product data using chatGPT model
     *
     * @param int $entityId The ID of the entity
     * @param int $storeId The ID of the store
     * @param string $modelType The type of the model
     * @param array $selectedAttributes The selected attributes for generating the product data
     * @param float $temperature The temperature for generating the product data
     * @param int $variants The number of result options to generate
     * @param string $content The content for generating the product data (optional)
     * @param array $context The context for generating the product data (optional)
     * @param string $processCode Unique code of the process
     * @return array                      The queued items
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function addToQueue(
        int    $entityId,
        int    $storeId,
        string $modelType,
        array  $selectedAttributes,
        float  $temperature,
        int    $variants,
        string $content,
        array  $context,
        string $processCode
    ): array;
}

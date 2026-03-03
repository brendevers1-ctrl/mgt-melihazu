<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types = 1);

namespace MageWorx\SeoAI\Model\Queue\QueueSaver;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Category as CategoryResource;
use Magento\Framework\Exception\NoSuchEntityException;
use MageWorx\OpenAI\Api\CallbackInterface;
use MageWorx\OpenAI\Api\OptionsInterface;
use MageWorx\OpenAI\Api\ResponseInterface;
use MageWorx\OpenAI\Exception\CallbackProcessingException;
use Psr\Log\LoggerInterface;

class CategoryCallback implements CallbackInterface
{
    protected LoggerInterface             $logger;
    protected CategoryRepositoryInterface $categoryRepository;
    protected CategoryResource            $categoryResource;

    public function __construct(
        CategoryRepositoryInterface $categoryRepository,
        CategoryResource            $categoryResource,
        LoggerInterface             $logger
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->logger             = $logger;
        $this->categoryResource   = $categoryResource;
    }

    public function execute(
        OptionsInterface  $options,
        ResponseInterface $response,
        ?array            $additionalData = []
    ): void {
        $categoryId = isset($additionalData['category_id']) ? (int)$additionalData['category_id'] : null;
        $storeId    = isset($additionalData['store_id']) ? (int)$additionalData['store_id'] : null;
        $dataKey    = isset($additionalData['data_key']) ? (string)$additionalData['data_key'] : null;

        if ($categoryId === null || $storeId === null || $dataKey === null) {
            $this->logger->error('Callback data is invalid', $additionalData);
            throw new CallbackProcessingException(__('Callback data is invalid'));
        }

        $content = $this->unpackResponseContent($response);

        $key = str_replace('improve_', '', $dataKey);

        try {
            $category = $this->categoryRepository->get($categoryId, $storeId)->setData($key, $content);
        } catch (NoSuchEntityException $e) {
            $this->logger->error($e->getMessage());
            // Skip the category if it does not exist
            return;
        }

        // Save through resource model to avoid the issue with the category store id in repository
        // (repository obtain store id from store manager)
        $this->categoryResource->saveAttribute($category, $key);
    }

    public function unpackResponseContent(ResponseInterface $response): string
    {
        $chatResponse = $response->getChatResponse();
        if (!empty($chatResponse['error'])) {
            $error =
                is_string($chatResponse['error']) ? $chatResponse['error'] : json_encode($chatResponse['error']);
            $this->logger->warning($error);

            throw new CallbackProcessingException(__('Callback data has error'));
        }

        return $response->getContent();
    }
}

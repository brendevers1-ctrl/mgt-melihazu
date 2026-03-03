<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types = 1);

namespace MageWorx\SeoAI\Model\Queue\QueueSaver;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Framework\Exception\NoSuchEntityException;
use MageWorx\OpenAI\Api\CallbackInterface;
use MageWorx\OpenAI\Api\OptionsInterface;
use MageWorx\OpenAI\Api\ResponseInterface;
use MageWorx\OpenAI\Exception\CallbackProcessingException;
use Psr\Log\LoggerInterface;

class ProductCallback implements CallbackInterface
{
    protected LoggerInterface            $logger;
    protected ProductRepositoryInterface $productRepository;
    protected ProductResource            $productResource;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        ProductResource            $productResource,
        LoggerInterface            $logger
    ) {
        $this->logger            = $logger;
        $this->productRepository = $productRepository;
        $this->productResource   = $productResource;
    }

    public function execute(
        OptionsInterface  $options,
        ResponseInterface $response,
        ?array            $additionalData = []
    ): void {
        $productId = isset($additionalData['product_id']) ? (int)$additionalData['product_id'] : null;
        $storeId   = isset($additionalData['store_id']) ? (int)$additionalData['store_id'] : null;
        $dataKey   = isset($additionalData['data_key']) ? (string)$additionalData['data_key'] : null;

        if ($productId === null || $storeId === null || $dataKey === null) {
            $this->logger->error('Callback data is invalid', $additionalData);
            throw new CallbackProcessingException(__('Callback data is invalid'));
        }

        $content = $this->unpackResponseContent($response);

        $key = str_replace('improve_', '', $dataKey);

        try {
            $product = $this->productRepository->getById($productId, false, $storeId)->setData($key, $content);
        } catch (NoSuchEntityException $e) {
            $this->logger->error($e->getMessage());
            // Skip the product if it does not exist
            return;
        }

        $this->productResource->saveAttribute($product, $key);
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

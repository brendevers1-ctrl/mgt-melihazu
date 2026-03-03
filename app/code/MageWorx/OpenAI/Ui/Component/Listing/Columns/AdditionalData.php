<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types = 1);

namespace MageWorx\OpenAI\Ui\Component\Listing\Columns;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Component\Listing\Columns\Column;

class AdditionalData extends Column
{
    protected Json                       $serializer;
    protected ProductRepositoryInterface $productRepository;
    protected StoreManagerInterface      $storeManager;
    protected CategoryRepository         $categoryRepository;

    public function __construct(
        ContextInterface           $context,
        UiComponentFactory         $uiComponentFactory,
        Json                       $serializer,
        ProductRepositoryInterface $productRepository,
        StoreManagerInterface      $storeManager,
        CategoryRepository         $categoryRepository,
        array                      $components = [],
        array                      $data = []
    ) {
        $this->serializer         = $serializer;
        $this->productRepository  = $productRepository;
        $this->storeManager       = $storeManager;
        $this->categoryRepository = $categoryRepository;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @TODO: add data pool to collect all possible data keys and labels from all modules
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $origData = $this->serializer->unserialize($item[$this->getData('name')]);
                $result   = '';

                if (isset($origData['product_id'])) {
                    try {
                        $product     = $this->productRepository->getById($origData['product_id']);
                        $productName = $product->getName();
                        $productSku  = $product->getSku();
                        $result      .= "<strong>Product name:</strong> {$productName} (SKU: {$productSku}, ID: {$origData['product_id']})<br>";
                    } catch (NoSuchEntityException $e) {
                        $result .= "<strong>Product ID:</strong> {$origData['product_id']} (Not found)<br>";
                    }
                }

                if (isset($origData['store_id'])) {
                    try {
                        $storeName = $this->storeManager->getStore($origData['store_id'])->getName();
                        $result    .= "<strong>Store name:</strong> {$storeName} (ID: {$origData['store_id']})<br>";
                    } catch (NoSuchEntityException $e) {
                        $result .= "<strong>Store ID:</strong> {$origData['store_id']} (Not found)<br>";
                    }
                }

                if (isset($origData['category_id'])) {
                    try {
                        $categoryName = $this->categoryRepository->get($origData['category_id'])->getName();
                        $result       .= "<strong>Category name:</strong> {$categoryName} (ID: {$origData['category_id']})<br>";
                    } catch (NoSuchEntityException $e) {
                        $result .= "<strong>Category ID:</strong> {$origData['category_id']} (Not found)<br>";
                    }
                }

                $item[$this->getData('name')] = $result;
            }
        }

        return $dataSource;
    }
}

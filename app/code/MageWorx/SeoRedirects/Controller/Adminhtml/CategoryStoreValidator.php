<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoRedirects\Controller\Adminhtml;

use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use MageWorx\SeoRedirects\Api\Data\CustomRedirectInterface;
use MageWorx\SeoRedirects\Api\Data\DpRedirectInterface;

class CategoryStoreValidator
{
    /**
     * @var CollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * CategoryStoreValidator constructor.
     *
     * @param CollectionFactory $categoryCollectionFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        CollectionFactory $categoryCollectionFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->storeManager              = $storeManager;
    }

    /**
     * @param DpRedirectInterface|CustomRedirectInterface $redirect
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function validate($redirect)
    {
        $storeId            = $redirect->getStoreId();
        $categoryId         = $redirect->getCategoryId();
        $priorityCategoryId = $redirect->getPriorityCategoryId();

        if ($storeId && ($categoryId || $priorityCategoryId)) {

            if ($categoryId && !$this->isCategoryExists($storeId, $categoryId)) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __(
                        'The selected category (ID#%1) does not belong to the store view (ID#%2) of the current redirect. Please, set another category.',
                        [(int)$categoryId, (int)$storeId]
                    )
                );
            }

            if ($priorityCategoryId && !$this->isCategoryExists($storeId, $priorityCategoryId)) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __(
                        'The selected priority category (ID#%1) does not belong to the store view (ID#%2) of the current redirect. Please, set another category.',
                        [(int)$categoryId, (int)$storeId]
                    )
                );
            }
        }
    }

    /**
     * @param int $storeId
     * @param int $categoryId
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function isCategoryExists($storeId, $categoryId)
    {
        $rootCategoryId = $this->storeManager->getStore($storeId)->getRootCategoryId();

        $categoryCollection = $this->categoryCollectionFactory->create();
        $categoryCollection->addAttributeToFilter('path', ['like' => "1/{$rootCategoryId}/%"]);
        $categoryCollection->setStoreId($storeId);
        $categoryCollection->addIdFilter($categoryId);

        return $categoryCollection->getAllIds() === [$categoryId];
    }
}

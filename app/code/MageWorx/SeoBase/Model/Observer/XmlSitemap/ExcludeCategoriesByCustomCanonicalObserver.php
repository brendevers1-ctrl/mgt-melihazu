<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoBase\Model\Observer\XmlSitemap;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use MageWorx\SeoBase\Api\Data\CustomCanonicalInterface;
use Magento\UrlRewrite\Controller\Adminhtml\Url\Rewrite;

class ExcludeCategoriesByCustomCanonicalObserver implements ObserverInterface
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category
     */
    protected $categoryResource;

    /**
     * @var \MageWorx\SeoBase\Model\ResourceModel\CustomCanonical\CollectionFactory
     */
    protected $customCanonicalCollectionFactory;

    /**
     * ExcludeCategoriesByCustomCanonicalObserver constructor.
     *
     * @param \MageWorx\SeoBase\Model\ResourceModel\CustomCanonical\CollectionFactory $customCanonicalCollectionFactory
     * @param \Magento\Catalog\Model\ResourceModel\Category $categoryResource
     */
    public function __construct(
        \MageWorx\SeoBase\Model\ResourceModel\CustomCanonical\CollectionFactory $customCanonicalCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Category $categoryResource
    ) {
        $this->customCanonicalCollectionFactory = $customCanonicalCollectionFactory;
        $this->categoryResource                 = $categoryResource;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Framework\DB\Select $select */
        $select  = $observer->getEvent()->getSelect();
        $storeId = $observer->getEvent()->getStoreId();

        if (!$select || !$storeId) {
            return;
        }

        $collection = $this->customCanonicalCollectionFactory->create();
        $collection->addFieldToFilter(CustomCanonicalInterface::SOURCE_ENTITY_TYPE, Rewrite::ENTITY_TYPE_CATEGORY);
        $collection->addFieldToSelect(CustomCanonicalInterface::SOURCE_ENTITY_ID);
        $collection->resetData();
        $categoryIds = array_column($collection->getData(), CustomCanonicalInterface::SOURCE_ENTITY_ID);

        if ($categoryIds) {
            $select->where('e.' . $this->categoryResource->getLinkField() . ' NOT IN(?)', $categoryIds);
        }
    }
}

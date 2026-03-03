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

class ExcludeProductsByCustomCanonicalObserver implements ObserverInterface
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    protected $productResource;

    /**
     * @var \MageWorx\SeoBase\Model\ResourceModel\CustomCanonical\CollectionFactory
     */
    protected $customCanonicalCollectionFactory;

    /**
     * ExcludeProductsByCustomCanonicalObserver constructor.
     *
     * @param \MageWorx\SeoBase\Model\ResourceModel\CustomCanonical\CollectionFactory $customCanonicalCollectionFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product $productResource
     */
    public function __construct(
        \MageWorx\SeoBase\Model\ResourceModel\CustomCanonical\CollectionFactory $customCanonicalCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Product $productResource
    ) {
        $this->customCanonicalCollectionFactory = $customCanonicalCollectionFactory;
        $this->productResource                  = $productResource;
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
        $collection->addFieldToFilter(CustomCanonicalInterface::SOURCE_ENTITY_TYPE, Rewrite::ENTITY_TYPE_PRODUCT);
        $collection->addFieldToSelect(CustomCanonicalInterface::SOURCE_ENTITY_ID);
        $collection->resetData();
        $productIds = array_column($collection->getData(), CustomCanonicalInterface::SOURCE_ENTITY_ID);

        if ($productIds) {
            $select->where('e.' . $this->productResource->getLinkField() . ' NOT IN(?)', $productIds);
        }
    }
}

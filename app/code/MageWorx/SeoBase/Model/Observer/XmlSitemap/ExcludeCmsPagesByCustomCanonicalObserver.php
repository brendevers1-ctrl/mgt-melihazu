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
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Cms\Api\Data\PageInterface;

class ExcludeCmsPagesByCustomCanonicalObserver implements ObserverInterface
{
    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * @var \MageWorx\SeoBase\Model\ResourceModel\CustomCanonical\CollectionFactory
     */
    protected $customCanonicalCollectionFactory;

    /**
     * ExcludeCmsPagesByCustomCanonicalObserver constructor.
     *
     * @param \MageWorx\SeoBase\Model\ResourceModel\CustomCanonical\CollectionFactory $customCanonicalCollectionFactory
     * @param \Magento\Cms\Model\ResourceModel\Page $pageResource
     */
    public function __construct(
        \MageWorx\SeoBase\Model\ResourceModel\CustomCanonical\CollectionFactory $customCanonicalCollectionFactory,
        MetadataPool $metadataPool
    ) {
        $this->customCanonicalCollectionFactory = $customCanonicalCollectionFactory;
        $this->metadataPool                     = $metadataPool;
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
        $collection->addFieldToFilter(CustomCanonicalInterface::SOURCE_ENTITY_TYPE, Rewrite::ENTITY_TYPE_CMS_PAGE);
        $collection->addFieldToSelect(CustomCanonicalInterface::SOURCE_ENTITY_ID);
        $collection->resetData();
        $pageIds = array_column($collection->getData(), CustomCanonicalInterface::SOURCE_ENTITY_ID);

        $entityMetadata = $this->metadataPool->getMetadata(PageInterface::class);

        if ($pageIds) {
            $select->where('main_table.' . $entityMetadata->getLinkField() . ' NOT IN(?)', $pageIds);
        }
    }
}

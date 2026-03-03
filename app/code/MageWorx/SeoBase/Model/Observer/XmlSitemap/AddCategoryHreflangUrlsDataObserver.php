<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoBase\Model\Observer\XmlSitemap;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class AddCategoryHreflangUrlsDataObserver implements ObserverInterface
{
    /**
     * @var \MageWorx\SeoBase\Helper\Hreflangs
     */
    protected $helperHreflangs;

    /**
     * @var \MageWorx\SeoBase\Model\ResourceModel\Catalog\Category\Hreflangs
     */
    protected $hreflangsUrlProvider;

    /**
     * AddCategoryHreflangUrlsDataObserver constructor.
     *
     * @param \MageWorx\SeoBase\Helper\Hreflangs $helperHreflangs
     * @param \MageWorx\SeoBase\Model\ResourceModel\Catalog\Category\Hreflangs $hreflangsUrlProvider
     */
    public function __construct(
        \MageWorx\SeoBase\Helper\Hreflangs $helperHreflangs,
        \MageWorx\SeoBase\Model\ResourceModel\Catalog\Category\Hreflangs $hreflangsUrlProvider
    ) {
        $this->helperHreflangs      = $helperHreflangs;
        $this->hreflangsUrlProvider = $hreflangsUrlProvider;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $container  = $observer->getEvent()->getContainer();
        $collection = $container->getCollection();
        $storeId    = $container->getStoreId();

        if ($collection && $storeId) {
            $altCodes          = $this->helperHreflangs->getHreflangFinalCodes('category', $storeId);
            $alternateUrlsData = $this->getAlternateUrlData($altCodes, $collection);

            $container->setData('alt_codes', $altCodes);
            $container->setData('alternate_urls_data', $alternateUrlsData);
        }

        return;
    }

    /**
     * @param array $altCodes
     * @param $collection
     * @return mixed
     */
    protected function getAlternateUrlData(array $altCodes, $collection)
    {
        if (!$altCodes) {
            return [];
        }

        $arrayTargetPath = [];
        foreach ($collection as $val) {
            $arrayTargetPath[$val->getId()] = $val->getTargetPath();
        }

        $alternateUrlsData = $this->hreflangsUrlProvider->getHreflangsData(
            array_keys($altCodes),
            array_keys($arrayTargetPath)
        );

        return $alternateUrlsData;
    }
}

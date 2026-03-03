<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoBase\Model\Observer\XmlSitemap;

use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class AddCmsPageHreflangUrlsDataObserver implements ObserverInterface
{
    /**
     * @var \MageWorx\SeoBase\Helper\Hreflangs
     */
    protected $helperHreflangs;

    /**
     * @var \MageWorx\SeoBase\Model\ResourceModel\Cms\Page\Hreflangs
     */
    protected $hreflangsUrlProvider;

    /**
     * AddCmsPageHreflangUrlsDataObserver constructor.
     *
     * @param \MageWorx\SeoBase\Helper\Hreflangs $helperHreflangs
     * @param \MageWorx\SeoBase\Model\ResourceModel\Cms\Page\Hreflangs $hreflangsUrlProvider
     */
    public function __construct(
        \MageWorx\SeoBase\Helper\Hreflangs $helperHreflangs,
        \MageWorx\SeoBase\Model\ResourceModel\Cms\Page\Hreflangs $hreflangsUrlProvider
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
        $item       = $container->getItem();
        $storeId    = $container->getStoreId();
        $isHomePage = $container->getIsHomePage();

        if ($item && $storeId) {
            $altCodes          = $this->helperHreflangs->getHreflangFinalCodes('cms', $storeId);
            $alternateUrlsData = $this->getAlternateUrlData($item, $altCodes, $isHomePage);

            $container->setData('alt_codes', $altCodes);
            $container->setData('alternate_urls_data', $alternateUrlsData);
        }

        return;
    }

    /**
     * @param DataObject $item
     * @param array $altCodes
     * @return array|false
     */
    protected function getAlternateUrlData($item, array $altCodes, $isHomePage)
    {
        if (!$altCodes) {
            return [];
        }

        if ($isHomePage) {
            $alternateUrls = $this->hreflangsUrlProvider->getHreflangsDataForHomePage(
                array_keys($altCodes),
                $item
            );
        } else {
            $alternateUrls = $this->hreflangsUrlProvider->getHreflangsData(
                array_keys($altCodes),
                $item
            );
        }

        return $alternateUrls;
    }
}

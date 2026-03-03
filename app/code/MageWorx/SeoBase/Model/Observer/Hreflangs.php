<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace MageWorx\SeoBase\Model\Observer;

use Magento\Framework\View\Page\Config;
use MageWorx\SeoBase\Model\HreflangsFactory as HreflangsFactory;
use MageWorx\SeoAll\Helper\SeoFeaturesStatusProvider;

/**
 * Observer class for hreflang URLs
 */
class Hreflangs implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \MageWorx\SeoBase\Model\HreflangFactory
     */
    protected $hreflangsFactory;

    /**
     * @var \Magento\Framework\View\Page\Config
     */
    protected $pageConfig;

    /**
     * @var $moduleName
     */
    protected $moduleName = 'SeoBase';

    /**
     * @var \MageWorx\SeoAll\Helper\SeoFeaturesStatusProvider $seoFeaturesStatusProvider
     */
    protected $seoFeaturesStatusProvider;

    /**
     * Hreflangs constructor.
     *
     * @param Config $pageConfig
     * @param HreflangsFactory $hreflangsFactory
     * @param SeoFeaturesStatusProvider $seoFeaturesStatusProvider
     */
    public function __construct(
        Config $pageConfig,
        HreflangsFactory $hreflangsFactory,
        SeoFeaturesStatusProvider $seoFeaturesStatusProvider
    ) {
        $this->pageConfig                = $pageConfig;
        $this->hreflangsFactory          = $hreflangsFactory;
        $this->seoFeaturesStatusProvider = $seoFeaturesStatusProvider;
    }

    /**
     * Set hreflang URLs to page config
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->seoFeaturesStatusProvider->getStatus($this->moduleName)) {
            return;
        }

        $fullActionName = $observer->getFullActionName();

        $arguments     = ['layout' => $observer->getLayout(), 'fullActionName' => $fullActionName];
        $hreflangModel = $this->hreflangsFactory->create($fullActionName, $arguments);

        if (!$hreflangModel) {
            return;
        }

        $hreflangUrls = $hreflangModel->getHreflangUrls();

        if (!$hreflangUrls) {
            return;
        }

        foreach ($hreflangUrls as $code => $hreflangUrl) {
            $this->pageConfig->addRemotePageAsset(
                $hreflangUrl,
                'alternate',
                ['attributes' => ['rel' => 'alternate', 'hreflang' => $code]]
            );
        }
    }
}
